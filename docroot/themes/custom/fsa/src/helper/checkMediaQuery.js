function checkMediaQuery () {
  var currentBreakpoint = null

  // Function for detecting breakpoint from the content of pseudo element
  var checkBreakpoint = function (element) {
    var breakpoint = window.getComputedStyle(document.body, ':after').content.replace(/['"]+/g, '')
    if (currentBreakpoint != null && currentBreakpoint === breakpoint) {
      return false
    }
    currentBreakpoint = breakpoint
    return currentBreakpoint
  }

  return checkBreakpoint()
}

module.exports = checkMediaQuery
