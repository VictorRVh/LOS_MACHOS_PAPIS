// tailwind.config.js

import colors from 'tailwindcss/colors';
import animationDelay from 'tailwindcss-animation-delay';

export default {
    darkMode: 'class',
    content: [
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './resources/**/*.vue',
    ],
    theme: {
        extend: {
            // ... tus screens, fontFamily, fontSize están bien ...
            screens: {
                xs: '470px',
                mobile: '420px',
            },
            fontFamily: {
                lora: ['Lora'],
                nabla: ['Nabla'],
                inter: ['Inter','sans-serif'],
            },
            fontSize: {
                xsm: '13px',
                '2xs': '11px',
                xxs: '10px',
            },

            colors: {
                // COLORES BASE
                'dark-bg': '#060818',
                'dark-color': 'rgba(255, 255, 255, 0.87)',
                'light-bg': '#ffffff',
                'light-color': '#213547',

                // TU PALETA PERSONALIZADA
                'cetpro': {
                    'light': '#338CBF',
                    'DEFAULT': '#006F9F',
                    'dark': '#00557F',
                    'text': '#FFFFFF',
                    
                },
                
                // COLORES DE ESTADO (active)
                active: {
                    DEFAULT: colors.blue[500],
                    light: colors.emerald[700],
                    dark: colors.emerald[500],
                    hover: colors.emerald[200],
                },
                
                // TUS OTROS COLORES 'cc-*'
                'cc-12': '#006F9F',
                'cc-18': '#006F9F',
                'cc-10': '#006F9F',
                'cc-13': '#006F9F',
                'cc-14': '#006F9F',
                'cc-19': '#006F9F',
                'cc-20': '#006F9F',
                'cc-21': '#3b3f5c',
                'cc-22': '#2563EB'
            },
            
            // ... tu transitionProperty, boxShadow, etc., están bien ...
            transitionProperty: {
                width: 'width',
                height: 'height',
                'max-height': 'max-height',
            },
            boxShadow: {
                google: '0px 8px 10px 1px rgba(0, 0, 0, 0.14), 0px 3px 14px 2px rgba(0, 0, 0, 0.12), 0px 5px 5px -3px rgba(0, 0, 0, 0.2)',
                'google-sm':
                    '0px 2px 2px 1px rgba(0, 0, 0, 0.14), 0px 2px 2px 2px rgba(0, 0, 0, 0.12), 0px 2px 2px 0px rgba(0, 0, 0, 0.2)',

                'google-dark':
                    '0px 8px 10px 1px rgba(14, 16, 9, 0.86), 0px 3px 14px 2px rgba(14, 16, 9, 0.88), 0px 5px 5px -3px rgba(14, 16, 9, 0.8)',
            },
            borderRadius: {
                sm: '4px',
            },
            animationDelay: {
                100: '100ms',
                // ... el resto de tus delays
            },
        },
    },
    plugins: [animationDelay],
    
};