import { createInertiaApp } from '@inertiajs/vue3';
import { createApp, h } from 'vue';
import './bootstrap';
import { ZiggyVue } from 'ziggy-js';

// Flag posé une seule fois par chargement de document (refresh ou premier accès).
// Réinitialisé à chaque rechargement de la page ; permet à AppLoader de n'afficher qu'au full load.
if (typeof window !== 'undefined') {
    window.__inertia_initial_load = true;
}

createInertiaApp({
    title: (title) => {
        return title;
    },
    resolve: (name) => {
        const pages = import.meta.glob('./Pages/**/*.vue', { eager: true });
        return pages[`./Pages/${name}.vue`];
    },
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .mount(el);
    },
    progress: {
        color: '#e50914',
    },
});
