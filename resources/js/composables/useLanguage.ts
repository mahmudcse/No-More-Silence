import { computed, ref } from 'vue';

export type SupportedLocale = 'bn' | 'en';

const currentLocale = ref<SupportedLocale>('bn');

// Initialize from localStorage or navigator if in browser
if (typeof window !== 'undefined') {
    const saved = localStorage.getItem('nms_locale') as SupportedLocale | null;
    if (saved === 'en' || saved === 'bn') {
        currentLocale.value = saved;
    }
}

export function useLanguage() {
    function setLocale(locale: SupportedLocale) {
        currentLocale.value = locale;
        if (typeof window !== 'undefined') {
            localStorage.setItem('nms_locale', locale);
        }
    }

    function toggleLocale() {
        setLocale(currentLocale.value === 'bn' ? 'en' : 'bn');
    }

    const isBn = computed(() => currentLocale.value === 'bn');

    return {
        locale: currentLocale,
        isBn,
        setLocale,
        toggleLocale,
    };
}
