import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

export function useTranslation() {
    const page = usePage();

    const locale = computed(() => page.props.locale ?? 'fr');
    const translations = computed(() => page.props.translations ?? {});

    function t(key, params = {}) {
        let text = translations.value[key] ?? key;
        Object.entries(params).forEach(([k, v]) => {
            text = text.replace(new RegExp(`{${k}}`, 'g'), v);
        });
        return text;
    }

    return { t, locale };
}
