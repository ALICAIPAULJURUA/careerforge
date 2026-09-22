import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['"Plus Jakarta Sans"', 'Inter', ...defaultTheme.fontFamily.sans],
                display: ['Fraunces', 'serif'],
                heading: ['Fraunces', 'serif'],
            },
            colors: {
                ink: {
                    DEFAULT: '#0F1F3C',
                    50: '#EFF3F9',
                    100: '#D9E2F0',
                    200: '#B3C5E1',
                    300: '#8DA8D2',
                    600: '#162A4D',
                    700: '#111F3A',
                    800: '#0F1F3C',
                    900: '#0B162B',
                },
                honey: {
                    DEFAULT: '#C6A15B',
                    50: '#FDF6E3',
                    100: '#F9E8B8',
                    600: '#B8944E',
                    700: '#A6843E',
                },
                sage: {
                    50: '#F0FDF9',
                    600: '#0F766E',
                    700: '#115E59',
                },
                surface: {
                    DEFAULT: '#F8F9FA',
                    50: '#F8FAFC',
                    100: '#F1F5F9',
                },
            },
            borderRadius: {
                'xl': '14px',
                '2xl': '18px',
            },
            boxShadow: {
                'soft': '0 4px 24px -4px rgba(15, 31, 60, 0.08), 0 2px 8px -2px rgba(15, 31, 60, 0.06)',
                'soft-lg': '0 8px 32px -8px rgba(15, 31, 60, 0.12), 0 4px 16px -4px rgba(15, 31, 60, 0.08)',
                'card': '0 1px 3px rgba(15, 31, 60, 0.08), 0 1px 2px rgba(15, 31, 60, 0.06)',
            },
            maxWidth: {
                '8xl': '88rem',
            }
        },
    },

    plugins: [forms],
};
