import PluginBase from '../abstracts/PluginBase';

/**
 * @typedef {Object} TriggerEntity
 * @property {string} trigger The selector for the trigger target element(s).
 * @property {string} condition The condition that must be met for the trigger to fire.
 * @property {string} action The action to perform when the trigger fires.
 * @property {string|undefined} parent The parent element with which to limit the trigger scope.
 * @property {string|number} priority The priority of the trigger event.
 * @property {HTMLElement[]} elements The target elements that this trigger applies to.
 */
/**
 * @typedef {Object} TriggerElement
 * @property {HTMLElement} element The target element.
 * @property {string} eventName The trigger event name.
 * @property {int} priority The trigger event priority.
 * @property {Function} event The trigger event function.
 */

/**
 * Trigger handler for HTML elements.
 *
 * This is a re-imagining of the Input.Trigger functionality in the original Winter CMS framework,
 * initialised through the `data-trigger` attributes.
 *
 * In addition to remaining backwards-compatible with the original Input.Trigger functionality, this
 * handler adds additional conditions and configuration for more flexible trigger usage.
 *
 * @see https://wintercms.com/docs/v1.2/ui/script/input-trigger
 *
 * @copyright 2024 Winter.
 * @author Ben Thomson <git@alfreido.com>
 */
export default class Trigger extends PluginBase {
    /**
     * Constructor.
     *
     * @param {HTMLElement} element
     */
    construct(element) {
        /**
         * The element this instance is attached to.
         */
        this.element = element;

        /**
         * @type {Map<string, Map<TriggerEntity>>} The triggers for this element.
         */
        this.triggers = new Map();

        /**
         * @type {Map<Element, Set<TriggerElement>>} A map of elements that trigger events.
         */
        this.events = new Map();

        /**
         * @type {Map<Element, Map<string, Function>>} A map of elements and their event connectors.
         */
        this.connectors = new Map();

        this.parseTriggers();

        if (this.triggers.size > 0) {
            this.resetEvents();
            this.createTriggerEvents();
            this.runEvents();

            this.snowboard.globalEvent('triggers.ready', this.element);
        }
    }

    /**
     * Destructor.
     */
    destruct() {
        this.resetEvents();
        super.destruct();
    }

    /**
     * Parses the element's data attributes and determines applicable triggers.
     *
     * Trigger data attributes must be in the format `data-trigger-[name]-[parameter]` for multiple
     * triggers, or `data-trigger-[parameter]` for single triggers.
     *
     * Supported parameters are:
     *  - `condition` or `where`: The condition that must be met for the trigger to fire.
     *  - `action`: The action to perform when the trigger fires.
     *  - `parent` or `closest-parent`: The parent element with which to limit the trigger scope.
     *
     * Internally, the trigger map uses the `trigger` parameter to store the trigger selector.
     */
    parseTriggers() {
        const { dataset } = this.element;
        this.triggers.clear();

        Object.keys(dataset).forEach((key) => {
            if (/-[A-Z]/.test(key)) {
                throw new Error(`Unable to convert camelCase to dash-style for data attribute: ${key}`);
            }

            const dashStyle = key.replace(/([A-Z])/g, (match) => `-${match.toLowerCase()}`);

            if (dashStyle !== 'trigger' && !dashStyle.startsWith('trigger-')) {
                return;
            }

            const triggerParts = /([a-z0-9\-.:_]+?)(?:(?:-)(closest-parent|condition|when|action|parent|priority))?$/i.exec(
                dashStyle.replace('trigger-', '').toLowerCase(),
            );

            let triggerName = null;
            let triggerType = null;

            if (
                ['trigger', 'condition', 'action', 'parent', 'when', 'closest'].indexOf(triggerParts[1]) !== -1
                && (triggerParts[1] !== 'closest' || (triggerParts[1] === 'closest' && triggerParts[2] === 'parent'))
            ) {
                // Support original trigger format
                triggerName = '__original';
                triggerType = (triggerParts[1] === 'closest') ? 'parent' : triggerParts[1];
            } else if (
                triggerParts[2] === undefined
                || ['closest-parent', 'condition', 'when', 'action', 'parent', 'priority'].indexOf(triggerParts[2]) !== -1
            ) {
                // Parse multi-trigger format
                [, triggerName] = triggerParts;
                switch (triggerParts[2]) {
                    case 'closest-parent':
                    case 'parent':
                        triggerType = 'parent';
                        break;
                    case 'condition':
                    case 'when':
                        triggerType = 'condition';
                        break;
                    case 'action':
                        triggerType = 'action';
                        break;
                    case 'priority':
                        triggerType = 'priority';
                        break;
                    default:
                        triggerType = 'trigger';
                        break;
                }
            }

            if (!this.triggers.has(triggerName)) {
                this.triggers.set(triggerName, new Map());
            }
            this.triggers.get(triggerName).set(triggerType, dataset[key]);

            // Remove trigger data attribute after parsing
            delete dataset[key];
        });

        // Validate triggers, and remove those that do not have at least a trigger selector, a
        // condition and an action, or are using invalid conditions or actions
        this.triggers.forEach((trigger, name) => {
            const elements = this.getSelectableElements(trigger);

            if (
                !trigger.has('trigger')
                || !trigger.has('condition')
                || !trigger.has('action')
                || elements.length === 0
                || !this.isValidCondition(trigger)
                || !this.isValidAction(trigger)
            ) {
                this.triggers.delete(name);
            } else {
                trigger.set('elements', elements);
                if (!trigger.has('priority')) {
                    trigger.set('priority', 100);
                }
            }
        });
    }

    /**
     * Parses a command given as either a condition or an action.
     *
     * Commands are formatted as: name:parameter1,parameter2,parameter3, although we also support
     * the old format of value[parameter1,parameter2,parameter3] for the `value` command only.
     *
     * If a parameter requires a comma within, the parameter should be wrapped in quotes.
     *
     * @param {string} command
     * @returns {name: string, parameters: string[]}
     */
    parseCommand(command) {
        // Support old-format value command (value[foo,bar])
        if (command.startsWith('value') && command.includes('[')) {
            const match = command.match(/[^[\]]+(?=])/g);
            const values = [];

            // Split values with commas
            match.forEach((value) => {
                if (!value.includes(',')) {
                    values.push(value.replace(/^['"]|['"]$/g, '').trim());
                    return;
                }

                const splitValues = value.replace(/("[^"]*")|('[^']*')/g, (quoted) => quoted.replace(/,/g, '|||'))
                    .split(',')
                    .map((splitValue) => splitValue.replace(/\|\|\|/g, ',').replace(/^['"]|['"]$/g, '').trim());

                values.push(...splitValues);
            });

            return {
                name: 'value',
                parameters: values,
            };
        }

        if (!command.includes(':')) {
            return {
                name: command,
                parameters: [],
            };
        }

        const [name, parameters] = command.split(':', 2);

        if (!parameters.includes(',')) {
            return {
                name,
                parameters: [parameters],
            };
        }

        const splitValues = parameters.replace(/("[^"]*")|('[^']*')/g, (quoted) => quoted.replace(/,/g, '|||'))
            .split(',')
            .map((splitValue) => splitValue.replace(/\|\|\|/g, ',').replace(/^['"]|['"]$/g, '').trim());

        return {
            name,
            parameters: splitValues,
        };
    }

    /**
     * Checks if any elements are accessible by the provided trigger.
     *
     * @param {Map<TriggerEntity>} trigger
     * @returns {HTMLElement[]}
     */
    getSelectableElements(trigger) {
        if (trigger.has('parent')) {
            return Array.from(this.element.closest(trigger.get('parent')).querySelectorAll(trigger.get('trigger')));
        }

        return Array.from(document.querySelectorAll(trigger.get('trigger')));
    }

    /**
     * Determines if the provided trigger condition is valid.
     *
     * @param {TriggerEntity} trigger
     * @returns {boolean}
     */
    isValidCondition(trigger) {
        return [
            'checked',
            'unchecked',
            'empty',
            'value',
            'oneof',
            'allof',
            'focus',
            'blur',
        ].includes(this.parseCommand(trigger.get('condition')).name.toLowerCase());
    }

    /**
     * Determines if the provided trigger action is valid.
     *
     * @param {TriggerEntity} trigger
     * @returns {boolean}
     */
    isValidAction(trigger) {
        return [
            'show',
            'hide',
            'enable',
            'disable',
            'empty',
            'value',
            'check',
            'uncheck',
            'class',
            'attr',
            'style',
        ].includes(this.parseCommand(trigger.get('action')).name.toLowerCase());
    }

    /**
     * Create trigger events on trigger and target elements.
     */
    createTriggerEvents() {
        this.triggers.forEach((trigger) => {
            const { name, parameters } = this.parseCommand(trigger.get('condition'));

            switch (name.toLowerCase()) {
                case 'value':
                case 'oneof':
                    this.createValueEvent(trigger, false, ...parameters);
                    break;
                case 'allof':
                    this.createValueEvent(trigger, true, ...parameters);
                    break;
                case 'empty':
                    this.createEmptyEvent(trigger);
                    break;
                case 'checked':
                case 'unchecked':
                    this.createCheckedEvent(trigger, (name === 'checked'), parameters[0] ?? undefined);
                    break;
                default:
            }
        });
    }

    /**
     * Creates a trigger that fires when the value of the target element(s) matches one of the
     * provided values.
     *
     * @param {TriggerEntity} trigger
     * @param  {...string} values
     */
    createValueEvent(trigger, all, ...values) {
        const supportedElements = new Set();

        trigger.get('elements').forEach((element) => {
            if (element.matches('input[type=button], input[type=file], input[type=image], input[type=reset], input[type=submit]')) {
                // Buttons and file inputs are unsupported
                return;
            }

            if (element.matches('input, select, textarea')) {
                supportedElements.add(element);
            }
        });

        const thisEvent = () => {
            const elementValues = new Set();

            supportedElements.forEach((element) => {
                if (element.matches('input[type=checkbox], input[type=radio]')) {
                    if (element.checked) {
                        elementValues.add(element.value);
                    }
                    return;
                }

                elementValues.add(element.value);
            });

            if (all) {
                if (values.every((value) => elementValues.has(value))) {
                    this.executeAction(trigger, true);
                } else {
                    this.executeAction(trigger, false);
                }
                return;
            }

            if (values.some((value) => elementValues.has(value))) {
                this.executeAction(trigger, true);
            } else {
                this.executeAction(trigger, false);
            }
        };

        supportedElements.forEach((element) => {
            if (element.matches('input[type=checkbox], input[type=radio]')) {
                this.addEvent(element, trigger, 'click', () => thisEvent());
                return;
            }

            this.addEvent(element, trigger, 'input', () => thisEvent());
        });
    }

    /**
     * Creates a trigger that fires when there is no value within the target element(s).
     *
     * @param {TriggerEntity} trigger
     */
    createEmptyEvent(trigger) {
        const supportedElements = new Set();

        trigger.get('elements').forEach((element) => {
            if (element.matches('input[type=button], input[type=image], input[type=reset], input[type=submit]')) {
                // Buttons and file inputs are unsupported
                return;
            }

            if (element.matches('input, select, textarea')) {
                supportedElements.add(element);
            }
        });

        const thisEvent = () => {
            const elementValues = new Set();

            supportedElements.forEach((element) => {
                if (element.matches('input[type=checkbox], input[type=radio]')) {
                    if (element.checked) {
                        elementValues.add(element);
                    }
                    return;
                }

                if (element.value.trim() !== '') {
                    elementValues.add(element);
                }
            });

            if (elementValues.size === 0) {
                this.executeAction(trigger, true);
            } else {
                this.executeAction(trigger, false);
            }
        };

        supportedElements.forEach((element) => {
            if (element.matches('input[type=checkbox], input[type=radio]')) {
                this.addEvent(element, trigger, 'click', () => thisEvent());
                return;
            }

            this.addEvent(element, trigger, 'input', () => thisEvent());
        });
    }

    /**
     * Creates a trigger that fires when a target element(s) is checked/unchecked.
     *
     * @param {TriggerEntity} trigger
     * @param {boolean} checked If the element should be checked or unchecked.
     * @param {number|undefined} atLeast The minimum number of elements that must be checked.
     *  Defaults to 1 if undefined.
     */
    createCheckedEvent(trigger, checked, atLeast = undefined) {
        const supportedElements = new Set();

        trigger.get('elements').forEach((element) => {
            // Only supports checkboxes and radio buttons
            if (element.matches('input[type=radio], input[type=checkbox]')) {
                supportedElements.add(element);
            }
        });

        const thisEvent = () => {
            const elementValues = new Set();

            supportedElements.forEach((element) => {
                if (checked === element.checked) {
                    elementValues.add(element);
                }
            });

            const atLeastCount = atLeast ? Number(atLeast) : 1;

            if (elementValues.size >= atLeastCount) {
                this.executeAction(trigger, true);
            } else {
                this.executeAction(trigger, false);
            }
        };

        supportedElements.forEach((element) => {
            this.addEvent(element, trigger, 'click', () => thisEvent());
        });
    }

    /**
     * Adds an event to an element.
     *
     * This registers the event in the `events` map for later usage and removal, and adds a
     * connector to the element for the event, so that we may enable prioritisation and control over
     * the firing of the events.
     *
     * @param {HTMLElement} element
     * @param {TriggerEntity} trigger
     * @param {string} eventName
     * @param {Function} callback
     */
    addEvent(element, trigger, eventName, callback) {
        if (!this.events.has(element)) {
            this.events.set(element, new Set());
        }

        const event = {
            element,
            eventName,
            priority: Number(trigger.get('priority')),
            event: callback,
        };

        this.events.get(element).add(event);

        if (!this.connectors.has(element)) {
            this.connectors.set(element, new Map());
        }
        if (!this.connectors.get(element).has(eventName)) {
            this.connectors.get(element).set(eventName, () => {
                const events = [];

                this.events.get(element).forEach((elementEvent) => {
                    if (elementEvent.eventName === eventName) {
                        events.push(elementEvent);
                    }
                });

                events
                    .sort((a, b) => a.priority - b.priority)
                    .forEach((elementEvent) => {
                        elementEvent.event();
                    });
            });

            element.addEventListener(eventName, this.connectors.get(element).get(eventName));
        }
    }

    runEvents() {
        this.connectors.forEach((elementConnectors) => {
            elementConnectors.forEach((connector) => {
                connector();
            });
        });
    }

    resetEvents() {
        this.connectors.forEach((elementConnectors, element) => {
            elementConnectors.forEach((connector, event) => {
                element.removeEventListener(event, connector);
            });
        });

        this.connectors.clear();
        this.events.clear();
    }

    executeAction(trigger, conditionMet) {
        const { name, parameters } = this.parseCommand(trigger.get('action'));

        switch (name) {
            case 'show':
            case 'hide':
                this.actionShow(trigger, (name === 'show') ? conditionMet : !conditionMet);
                break;
            default:
        }
    }

    actionShow(trigger, show) {
        if (show && getComputedStyle(this.element).display === 'none') {
            this.element.classList.remove('hide');

            if (!this.element.dataset.originalDisplay) {
                this.element.style.display = 'block';
            } else {
                this.element.style.display = this.element.dataset.originalDisplay;
            }

            delete this.element.dataset.originalDisplay;

            this.afterAction(trigger);
        } else if (!show && getComputedStyle(this.element).display !== 'none') {
            this.element.classList.add('hide');

            this.element.dataset.originalDisplay = getComputedStyle(this.element).display;
            this.element.style.display = 'none';

            this.afterAction(trigger);
        }
    }

    afterAction(trigger) {
        this.snowboard.debug('Trigger fired', this.element, trigger);
        this.snowboard.globalEvent('trigger.fired', this.element, trigger);
    }
}
