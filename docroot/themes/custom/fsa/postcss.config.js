module.exports = {
  plugins: {
    'postcss-import': {},
    'postcss-mixins': {},
    'postcss-cssnext': {
      features: {
        customProperties: {
          preserve: false,
          warnings: false
        },
        rem: false,
        nesting: false
      }
    },
    'postcss-nested': {},
    'postcss-normalize': {},
    'postcss-discard-comments': {}
  }
}
