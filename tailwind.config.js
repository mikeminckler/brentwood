const { colors } = require('tailwindcss/defaultTheme');
  
module.exports = {
  theme: {
    extend: {
      fontFamily: {
        'roboto': ['Roboto'],
        'oswald': ['Oswald'],
      },
      fontSize: {
        '7xl': '5rem',
        '8xl': '6rem',
      },
      colors: {
        'primary': '#c8272c',
        'primaryHover': '#cf2328',
        'translucent': 'rgba(55,55,55,0.5)',
        'wash': 'rgba(255,255,255,0.75)'
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
  plugins: []
}
