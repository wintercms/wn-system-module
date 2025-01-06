// eslint-disable-next-line import/no-extraneous-dependencies
import { createApp } from 'vue';
import PluginUpdates from './components/PluginUpdates.vue';
import { winterRequestPlugin } from './utils/winter-request';

const onReady = (callback) => {
    if (document.readyState === 'complete') {
        callback();
    } else {
        window.addEventListener('load', callback);
    }
};

onReady(() => {
    const element = document.querySelector('#updates-app');

    const app = createApp({
        ...element.dataset,
        components: { PluginUpdates },
    });

    app.use(winterRequestPlugin);

    app.mount(element);
});
