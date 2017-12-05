module.exports = {
  plugins: {
    'postcss-import': {},
    'postcss-mixins': {},
    'postcss-cssnext': {
      features: {
        customProperties: {
          preserve: true,
          warnings: false,
          variables: {
            padding: "2em",
          }
        },
        rem: false,
        nesting: false,
      }
    },
    'postcss-nested': {},
    'postcss-normalize': {},
  },
};
