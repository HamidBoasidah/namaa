module.exports = {
	darkMode: 'class',
	content: [
		'./vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
		'./storage/framework/views/*.php',
		'./resources/**/*.blade.php',
		'./resources/**/*.js',
		'./resources/**/*.vue',
	],
	theme: {
    extend: {
        fontFamily: {
            sans: ['Figtree', ...require('tailwindcss/defaultTheme').fontFamily.sans],
        },
        colors: {
            testcolor: '#ff0000',
            // Brand palette (CASB / نماء) – from brand guide
            brand: {
                dark: '#12392A',
                forest: '#2F684F',
                muted: '#A58DA0',
                pale: '#A0BDA0',
                offwhite: '#F5F7F6',
                black: '#0E0E0E',
            },
            }
        }
    },
	plugins: [
		require('tailwindcss-rtl'),
		require('@tailwindcss/forms'),
	],
}
