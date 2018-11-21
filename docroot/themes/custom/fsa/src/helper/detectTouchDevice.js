function detectTouchDevice () {
  // Add class if touch device
  document.addEventListener('touchstart', function addtouchclass () {
    document.documentElement.classList.add('is-touch')
    document.removeEventListener('touchstart', addtouchclass, false)
  }, false)
}

module.exports = detectTouchDevice
