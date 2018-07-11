import doScrolling from '../../helper/scrollToElement'

function toc () {
  // Set 'tabindex' attribute to all scroll targets so that currentHeading.focus() works properly
  document
      .querySelector('.field__body')
      .querySelectorAll('h1[id], h2[id], h3[id], h4[id], h5[id], h6[id]')
      .forEach(x => x.setAttribute('tabindex', '-1'))

  // Table of contents
  const tableOfContentsElements = [...document.querySelectorAll('.js-toc-list')]

  // Check everything found
  if (tableOfContentsElements.length <= 0) {
    return false
  }

  // Get children
  const tocNavigationItems = [...tableOfContentsElements[0].children]

  // Navigation items
  for (let i = 0; i < tocNavigationItems.length; i++) {
    let thisTocNavigationItem = tocNavigationItems[i]

    thisTocNavigationItem.addEventListener('click', function (e) {
      e.preventDefault()

      let id = this.children[0].href.substr(this.children[0].href.indexOf('#') + 1)
      let currentHeading = document.getElementById(id)

      // Scroll
      doScrolling(currentHeading, 1000, -20)
      currentHeading.focus()
    })
  }
}

module.exports = toc
