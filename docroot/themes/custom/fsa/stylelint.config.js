module.exports = {
  extends: 'stylelint-config-standard',
  'rules': {
    'no-descending-specificity': null,
    'selector-pseudo-element-colon-notation': 'single',
    'at-rule-no-unknown': [ true, {
      'ignoreAtRules': [
        'mixin',
        'extends',
        'ignores'
      ]
    }]
  }
}
