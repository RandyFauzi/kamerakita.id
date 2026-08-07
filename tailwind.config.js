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
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                indigo: defaultTheme.colors.blue, // Override indigo with vibrant blue
                // Premium HSL-based palette
                premium: {
                    50: 'hsl(230, 80%, 98%)',
                    100: 'hsl(230, 80%, 95%)',
                    500: 'hsl(210, 100%, 60%)', // Adjusted to bright blue
                    600: 'hsl(210, 100%, 50%)', // Adjusted to bright blue
                    700: 'hsl(210, 100%, 40%)',
                    900: 'hsl(210, 100%, 20%)',
                },
                slate: {
                    ...defaultTheme.colors.slate,
                    950: '#0f172a',
                }
            }
        },
    },

    plugins: [forms],
};
