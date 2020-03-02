const { colors } = require('tailwindcss/defaultTheme');
  
module.exports = {
  theme: {
    extend: {
      colors: {
      },
      zIndex: {
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
        '50p': '50%',
        '66p': '66%',
        '75p': '75%',
        '100p': '100%',
      }
    },
  },
  variants: {
    backgroundColor: ['responsive', 'odd', 'hover', 'focus'],
    margin: ['responsive', 'first', 'last'],
    borderRadius: ['responsive', 'first', 'last'],
  },
  plugins: []
}
