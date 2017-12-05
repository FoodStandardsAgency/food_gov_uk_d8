import stickyElement from '../core/helper/stickyElement';
import scrollToElement from '../core/helper/scrollToElement';

// Sticky element
const container = [...document.querySelectorAll('.js-sticky-container')];
const stickyElem = [...document.querySelectorAll('.js-sticky-element')];
if (container != null || stickyElem != null) {
  stickyElement(container, stickyElem);
}

const scrollElementArray = [...document.querySelectorAll('.js-scroll')];
if (scrollElementArray != null) {
  for (let i = 0; i < scrollElementArray.length; i++) {
    const thisTocNavigationItem = scrollElementArray[i];

    thisTocNavigationItem.addEventListener('click', function(e) {
      e.preventDefault();

      let id = this.href.substr(this.href.indexOf("#") + 1);
      let currentHeading = document.getElementById(id);

      // Scroll
      scrollToElement(currentHeading, 1000, -20);
    });
  }
}