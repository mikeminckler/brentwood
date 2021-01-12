const colors = require('tailwindcss/colors');
  
module.exports = {
  purge: {
      content: [
        './resources/**/*.scss',
        './resources/**/*.vue',
        './resources/**/*.js',
        './resources/**/*.php',
      ],
      options: {
        safelist: ['grid-cols-1',
            'grid-cols-1',
            'grid-cols-2', 
            'grid-cols-3', 
            'grid-cols-4',
            'md:grid-cols-1',
            'md:grid-cols-2', 
            'md:grid-cols-3', 
            'md:grid-cols-4',

            'col-span-1',
            'col-span-2',
            'col-span-3',
            'col-span-4',

            'row-start-1',
            'col-start-1',
            'row-start-2',
            'col-start-2',
            'row-start-3',
            'col-start-3',
            'row-start-4',
            'col-start-4',

            'md:pb-200p',
            'md:pb-150p',
            'md:pb-100p',
            'md:pb-75p',
            'md:pb-66p',
            'md:pb-50p',
            'md:pb-40p',
            'md:pb-33p',
            'md:pb-25p',
            'md:pb-video'
        ],
      },
  },
  theme: {
    extend: {
      fontFamily: {
        'roboto': ['Roboto'],
        'oswald': ['Oswald'],
      },
      colors: {
        'primary': '#c8272c',
        'primaryHover': '#cf2328',
      },
      flex: {
        '2': '2 2 0%',
        '3': '3 3 0%',
      },
      zIndex: {
        '1': '1',
        '2': '2',
        '3': '3',
        '4': '4',
        '5': '5',
        '6': '6',
        '15': '15',
        '25': '25',
        '35': '35',
        '45': '45',
        '55': '55',
      },
      padding: {
        '25p': '25%',
        '33p': '33%',
        '40p': '40%',
        '50p': '50%',
        '66p': '66%',
        '75p': '75%',
        '100p': '100%',
        '150p': '150%',
        '200p': '200%',
        'video': '56.25%',
      },
      margin: {
        '33p': '33%',
      },
      maxHeight: {
       '0': '0',
      },
      cursor: {
        'zoom-in': 'zoom-in',
      }
    },
  },
  variants: {
    backgroundColor: ['responsive', 'odd', 'hover', 'focus'],
    margin: ['responsive', 'first', 'last'],
    padding: ['responsive', 'first', 'last'],
  },
  plugins: [],
  future: {
    removeDeprecatedGapUtilities: true,
    purgeLayersByDefault: true,
  },
}
