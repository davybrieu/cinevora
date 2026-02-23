import { createInertiaApp, Link, Head } from "@inertiajs/vue3";
import createServer from "@inertiajs/vue3/server";
import { renderToString } from "@vue/server-renderer";
import { createSSRApp, h } from "vue";
import { ZiggyVue } from 'ziggy-js';
import { Ziggy } from './ziggy.js';

createServer(
    (page) =>
        createInertiaApp({
            page,
            render: renderToString,
            title: (title) => {
                return title;
            },
            resolve: (name) => {
                const pages = import.meta.glob("./Pages/**/*.vue", {
                    eager: true,
                });
                return pages[`./Pages/${name}.vue`];
            },
            setup({ App, props, plugin }) {
                return createSSRApp({
                    render: () => h(App, props),
                }).use(plugin).use(ZiggyVue, Ziggy)
                    .component('Link', Link)
                    .component('Head', Head);
            },
            progress: {
                color: "#193cb8",
            },
        }),
    Number(import.meta.env.VITE_INERTIA_SSR_PORT) || 13726,
);
