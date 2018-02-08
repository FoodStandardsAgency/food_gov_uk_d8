import debounce from '../../helper/debounce'

function fhrs () {
  // All the toggle buttons
  const mainSearchElementArray = [...document.querySelectorAll('.js-main-search-input')]

  // Check everything found
  if (mainSearchElementArray <= 0) {
    return
  }

  const addActiveClass = (element) => {
    element.classList.add('is-active')
  }

  const removeActiveClass = (element) => {
    element.classList.remove('is-active')
  }

  mainSearchElementArray.forEach((element) => {
    const classHandler = debounce(function () {
      if (element.value.length > 0 || element === document.activeElement) {
        removeActiveClass(element)
      } else {
        addActiveClass(element)
      }
    }, 250)

    element.addEventListener('keyup', classHandler)

    element.addEventListener('focus', function (event) {
      removeActiveClass(element)
    }, true)

    element.addEventListener('blur', function (event) {
      addActiveClass(element)
    }, true)
  })
}

module.exports = fhrs
