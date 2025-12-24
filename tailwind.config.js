import defaultTheme from "tailwindcss/defaultTheme";
import forms from "@tailwindcss/forms";

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        "./vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php",
        "./storage/framework/views/*.php",
        "./resources/views/**/*.blade.php",
        "./node_modules/flowbite/**/*.js",
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ["Figtree", ...defaultTheme.fontFamily.sans],
            },
            colors: {
                bpsBlue: "#20246c",
                primary: {
                    50: "rgb(var(--color-primary-50) / <alpha-value>)",
                    100: "rgb(var(--color-primary-100) / <alpha-value>)",
                    200: "rgb(var(--color-primary-200) / <alpha-value>)",
                    300: "rgb(var(--color-primary-300) / <alpha-value>)",
                    400: "rgb(var(--color-primary-400) / <alpha-value>)",
                    500: "rgb(var(--color-primary-500) / <alpha-value>)",
                    600: "rgb(var(--color-primary-600) / <alpha-value>)",
                    700: "rgb(var(--color-primary-700) / <alpha-value>)",
                    800: "rgb(var(--color-primary-800) / <alpha-value>)",
                    900: "rgb(var(--color-primary-900) / <alpha-value>)",
                },
                // Repeat for Secondary
                secondary: {
                    50: "rgb(var(--color-secondary-50) / <alpha-value>)",
                    // ... you can map all 50-900 here same as above ...
                    500: "rgb(var(--color-secondary-500) / <alpha-value>)",
                    700: "rgb(var(--color-secondary-700) / <alpha-value>)",
                },
                // Repeat for Tertiary
                tertiary: {
                    50: "rgb(var(--color-tertiary-50) / <alpha-value>)",
                    500: "rgb(var(--color-tertiary-500) / <alpha-value>)",
                },
            },
        },
    },

    plugins: [require("flowbite/plugin"), forms],
};
