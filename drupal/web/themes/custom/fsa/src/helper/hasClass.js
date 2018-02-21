function hasClass (elem, cls) {
  var str = ' ' + elem.className + ' '
  var testCls = ' ' + cls + ' '
  return (str.indexOf(testCls) !== -1)
}

module.exports = hasClass
