import doScrolling from './scrollToElement';

function tableOfContents(tableOfContentsElement, contentElement) {
  // Get children
  const tocNavigationItems = tableOfContentsElement[0].children[0].children,
  tocContentItems = contentElement[0].children;

  for (let i = 0; i < tocNavigationItems.length; i++) {
    let thisTocNavigationItem = tocNavigationItems[i];
    thisTocNavigationItem.addEventListener("click", function(e) {
      e.preventDefault();

      let id = this.children[0].href.substr(this.children[0].href.indexOf("#") + 1);
      let currentHeading = document.getElementById(id);

      // Scroll
      doScrolling(currentHeading, 1000, -20);
    });
  }

  for (let i = 0; i < tocContentItems.length; i++) {
    if (tocContentItems[i].tagName == 'H2') {
      //console.log(tocContentItems[i].id);
    }
  }

}

module.exports = tableOfContents;
