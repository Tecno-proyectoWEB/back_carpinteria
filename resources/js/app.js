import './bootstrap';
import '../css/app.css';

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from '@tofandel/ziggy-js/dist/vue.m.js';

const appName = import.meta.env.VITE_APP_NAME || 'Carpintería';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) })
            .use(plugin);

        // Configurar Ziggy con las rutas compartidas desde Inertia
        try {
            const ziggy = props.initialPage?.props?.ziggy;
            if (ziggy) {
                // Poner Ziggy en window para que el helper pueda accederlo
                if (typeof window !== 'undefined') {
                    window.Ziggy = ziggy;
                }
                app.use(ZiggyVue, ziggy);
            } else {
                app.use(ZiggyVue);
            }
        } catch (e) {
            console.warn('Error configurando Ziggy:', e);
            app.use(ZiggyVue);
        }

        return app.mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});
