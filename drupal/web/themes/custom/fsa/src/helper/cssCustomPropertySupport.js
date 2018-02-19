/* global CSS */
function cssCustomPropertySupport () {
  return (window.CSS && CSS.supports('color', 'var(--primary)'))
}

module.exports = cssCustomPropertySupport
