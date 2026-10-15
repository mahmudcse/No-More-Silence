import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    darkMode: 'class',

    theme: {
        extend: {
            fontFamily: {
                sans: [
                    '-apple-system',
                    'BlinkMacSystemFont',
                    '"SF Pro Text"',
                    '"SF Pro Display"',
                    '"SF Pro"',
                    '"Helvetica Neue"',
                    'Helvetica',
                    'Arial',
                    'Hind Siliguri',
                    ...defaultTheme.fontFamily.sans,
                ],
            },
            boxShadow: {
                'glass': '0 8px 32px 0 rgba(0, 0, 0, 0.04)',
                'glass-lg': '0 16px 48px 0 rgba(0, 0, 0, 0.07)',
                'glass-dark': '0 8px 32px 0 rgba(0, 0, 0, 0.35)',
            },
            colors: {
                // Editorial ink & steel dark palette (replaces generic AI blue-slate)
                slate: {
                    950: '#090a0f', // Deep archival ink
                    900: '#11131b', // Surface card background
                    850: '#161924', // Elevated surface
                    800: '#1d2230', // Razor-sharp card border
                    700: '#2b3246', // Muted border
                    600: '#424c68', // Subtle utility
                    500: '#616f92', // Muted text
                    400: '#8e9bb4', // Secondary copy
                    300: '#c5cee0', // Primary high-contrast body
                    200: '#e1e6f0', // Clean body text
                    100: '#f1f4f9', // Clean headline white
                    50: '#f8fafc',
                },
                // Restrained, serious crimson (replaces generic AI neon pink)
                rose: {
                    950: '#2a0508', // Deep red-black
                    900: '#4c080e', // Sober alert background
                    800: '#730d15', // Authoritative red border
                    700: '#9b111c', // Deep crimson accent
                    600: '#b91c1c', // Primary action button (clean red-700)
                    500: '#dc2626', // Hover state
                    400: '#ef4444', // Accent text
                    300: '#f87171', // Light accent
                    200: '#fca5a5',
                    100: '#fee2e2',
                    50: '#fff1f2',
                },
            },
        },
    },

    plugins: [forms],
};
