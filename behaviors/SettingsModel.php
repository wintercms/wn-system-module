<?php namespace System\Behaviors;

use App;
use Artisan;
use Cache;
use Log;
use Exception;
use Illuminate\Database\QueryException;
use System\Classes\ModelBehavior;
use Winter\Storm\Database\Model;

/**
 * Settings model extension
 *
 * Add this the model class definition:
 *
 *     public $implement = ['System.Behaviors.SettingsModel'];
 *     public $settingsCode = 'author_plugin_code';
 *     public $settingsFields = 'fields.yaml';
 *
 * Optionally:
 *
 *     public $settingsCacheTtl = 1440;
 *
 */
class SettingsModel extends ModelBehavior
{
    use \System\Traits\ConfigMaker;

    protected $recordCode;
    protected $fieldConfig;
    protected $fieldValues = [];

    /**
     * @var integer Settings cache TTL, in seconds.
     */
    protected int $cacheTtl = 1440;

    /**
     * @var array Internal cache of model objects.
     */
    private static $instances = [];

    /**
     * @inheritDoc
     */
    protected $requiredProperties = ['settingsFields', 'settingsCode'];

    /**
     * Constructor
     */
    public function __construct($model)
    {
        parent::__construct($model);

        $this->model->setTable('system_settings');
        $this->model->jsonable(['value']);
        $this->model->guard([]);
        $this->model->timestamps = false;

        $this->configPath = $this->guessConfigPathFrom($model);

        /*
         * Access to model's overrides is unavailable, using events instead
         */
        $this->model->bindEvent('model.afterFetch', [$this, 'afterModelFetch']);
        $this->model->bindEvent('model.beforeSave', [$this, 'beforeModelSave']);
        $this->model->bindEvent('model.afterSave', [$this, 'afterModelSave']);
        $this->model->bindEvent('model.setAttribute', [$this, 'syncModelAttribute']);
        $this->model->bindEvent('model.saveInternal', [$this, 'saveModelInternal']);

        /*
         * Parse the config
         */
        $this->recordCode = $this->model->settingsCode;

        if ($this->model->propertyExists('settingsCacheTtl')) {
            $this->cacheTtl = (int) $this->model->settingsCacheTtl;
        }
    }

    /**
     * Create an instance of the settings model, intended as a static method
     */
    public function instance(): Model
    {
        if (isset(self::$instances[$this->recordCode])) {
            return self::$instances[$this->recordCode];
        }

        if (!$item = $this->getSettingsRecord()) {
            $this->model->initSettingsData();
            $this->afterModelFetch();
            $item = $this->model;
        }

        return self::$instances[$this->recordCode] = $item;
    }

    /**
     * Reset the settings to their defaults, this will delete the record model
     */
    public function resetDefault()
    {
        if ($record = $this->getSettingsRecord()) {
            $record->delete();
            unset(self::$instances[$this->recordCode]);
            Cache::forget($this->getCacheKey());
        }
    }

    /**
     * Checks if the model has been set up previously, intended as a static method
     */
    public function isConfigured(): bool
    {
        return App::hasDatabase() && $this->getSettingsRecord() !== null;
    }

    /**
     * Returns the raw Model record that stores the settings.
     * @return Model
     */
    public function getSettingsRecord()
    {
        $query = $this->model->where('item', $this->recordCode);

        if ($this->cacheTtl > 0) {
            $query = $query->remember($this->cacheTtl, $this->getCacheKey());
        }

        try {
            $record = $query->first();
        } catch (QueryException $ex) {
            // SQLSTATE[42S02]: Base table or view not found - migrations haven't run yet
            if ($ex->getCode() === '42S02') {
                $record = null;
                traceLog($ex);
            }
        }

        return $record ?: null;
    }

    /**
     * Set a single or array key pair of values, intended as a static method
     */
    public function set($key, $value = null)
    {
        $data = is_array($key) ? $key : [$key => $value];
        $obj = self::instance();
        $obj->fill($data);
        return $obj->save();
    }

    /**
     * Helper for getSettingsValue, intended as a static method
     */
    public function get($key, $default = null)
    {
        return $this->instance()->getSettingsValue($key, $default);
    }

    /**
     * Get a single setting value, or return a default value
     */
    public function getSettingsValue($key, $default = null)
    {
        if (array_key_exists($key, $this->fieldValues)) {
            return $this->fieldValues[$key];
        }

        return array_get($this->fieldValues, $key, $default);
    }

    /**
     * Set a single setting value, if allowed.
     */
    public function setSettingsValue($key, $value)
    {
        if ($this->isKeyAllowed($key)) {
            return;
        }

        $this->fieldValues[$key] = $value;
    }

    /**
     * Sync the provided attribute into the settings value
     */
    protected function syncModelAttribute($key)
    {
        $this->setSettingsValue($key, $this->model->getAttribute($key));
    }

    /**
     * Default values to set for this model, override
     */
    public function initSettingsData()
    {
    }

    /**
     * Populate the field values from the database record.
     */
    public function afterModelFetch()
    {
        $this->fieldValues = $this->model->value ?: [];
        $this->model->attributes = array_merge($this->fieldValues, $this->model->attributes);
    }

    /**
     * Internal save method for the model
     * @return void
     */
    public function saveModelInternal()
    {
        // Purge the field values from the attributes
        $this->model->attributes = array_diff_key($this->model->attributes, $this->fieldValues);
    }

    /**
     * Before the model is saved, ensure the record code is set
     * and the jsonable field values
     */
    public function beforeModelSave()
    {
        $this->model->item = $this->recordCode;
        if ($this->fieldValues) {
            $this->model->value = $this->fieldValues;
        }
    }

    /**
     * After the model is saved, clear the cached query entry
     * and restart queue workers so they have the latest settings
     * @return void
     */
    public function afterModelSave()
    {
        Cache::forget($this->getCacheKey());

        try {
            Artisan::call('queue:restart');
        } catch (Exception $e) {
            Log::warning($e->getMessage());
        }

        $this->afterModelFetch();
    }

    /**
     * Checks if a key is legitimate or should be added to
     * the field value collection
     */
    protected function isKeyAllowed($key)
    {
        /*
         * Let the core columns through
         */
        if ($key == 'id' || $key == 'value' || $key == 'item') {
            return true;
        }

        /*
         * Let relations through
         */
        if ($this->model->hasRelation($key)) {
            return true;
        }

        return false;
    }

    /**
     * Returns the field configuration used by this model.
     */
    public function getFieldConfig()
    {
        if ($this->fieldConfig !== null) {
            return $this->fieldConfig;
        }

        return $this->fieldConfig = $this->makeConfig($this->model->settingsFields);
    }

    /**
     * Returns a cache key for this record.
     */
    protected function getCacheKey()
    {
        return 'system::settings.'.$this->recordCode;
    }

    /**
     * Clears the internal memory cache of model instances.
     * @return void
     */
    public static function clearInternalCache()
    {
        static::$instances = [];
    }
}
