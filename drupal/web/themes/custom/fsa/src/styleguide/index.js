import stickyElement from '../helper/stickyElement'
import scrollToElement from '../helper/scrollToElement'

// Fix issues with 100vh flex height
document.querySelector('.navigation-wrapper__main').style.height = '82px'
document.querySelectorAll('.promo-listing__item').forEach(promo => {
  promo.style.height = '400px'
})
document.querySelectorAll('.field__field_content_reference__item').forEach(el => {
  el.style.height = '130.5px'
  el.style.marginBottom = '17.5px'
})

// Sticky element
const container = [...document.querySelectorAll('.js-sticky-container')]
const stickyElem = [...document.querySelectorAll('.js-sticky-element')]
if (container != null || stickyElem != null) {
  stickyElement(container, stickyElem)
}

const scrollElementArray = [...document.querySelectorAll('.js-scroll')]
if (scrollElementArray != null) {
  for (let i = 0; i < scrollElementArray.length; i++) {
    const thisTocNavigationItem = scrollElementArray[i]

    thisTocNavigationItem.addEventListener('click', function (e) {
      e.preventDefault()

      let id = this.href.substr(this.href.indexOf('#') + 1)
      let currentHeading = document.getElementById(id)

      // Scroll
      scrollToElement(currentHeading, 1000, -20)
    })
  }
}
