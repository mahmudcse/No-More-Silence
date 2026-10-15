import { computed, ref } from 'vue';

export type Theme = 'dark' | 'light';

const currentTheme = ref<Theme>('light');

// Initialize from localStorage if in browser
if (typeof window !== 'undefined') {
    const saved = localStorage.getItem('nms_theme') as Theme | null;
    if (saved === 'dark' || saved === 'light') {
        currentTheme.value = saved;
    } else {
        // Default is light
        currentTheme.value = 'light';
    }
    applyTheme(currentTheme.value);
}

function applyTheme(theme: Theme) {
    if (typeof document === 'undefined') return;

    if (theme === 'dark') {
        document.documentElement.classList.add('dark');
    } else {
        document.documentElement.classList.remove('dark');
    }

    const metaThemeColor = document.querySelector('meta[name="theme-color"]');
    if (metaThemeColor) {
        metaThemeColor.setAttribute(
            'content',
            theme === 'dark' ? '#090a0f' : '#f8fafc',
        );
    }
}

export function useTheme() {
    function setTheme(theme: Theme) {
        currentTheme.value = theme;
        if (typeof window !== 'undefined') {
            localStorage.setItem('nms_theme', theme);
            applyTheme(theme);
        }
    }

    function toggleTheme() {
        setTheme(currentTheme.value === 'dark' ? 'light' : 'dark');
    }

    const isDark = computed(() => currentTheme.value === 'dark');

    return {
        theme: currentTheme,
        isDark,
        setTheme,
        toggleTheme,
    };
}
