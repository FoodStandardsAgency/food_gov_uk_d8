import doScrolling from '../../helper/scrollToElement';

function toc() {

  // Table of contents
  const tableOfContentsElements = [...document.querySelectorAll('.js-toc-list')];
  // const contentElements = [...document.querySelectorAll('.toc-filter')];

  // Check everything found
  if (tableOfContentsElements.length <= 0) {
    return false;
  }

  // Get children
  const tocNavigationItems = [...tableOfContentsElements[0].children];

  // tocContentItems = contentElements[0].children;


  // for (let i = 0; i < tocContentItems.length; i++) {
  //   if (tocContentItems[i].tagName == 'H2') {
  //     console.log(tocContentItems[i].id);
  //   }
  // }
  //
  // const containers = [],
  // sections = [],
  // naviItems = [];

  // Navigation items
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

  // // Content items
  // for (var i = 0; i < contentElements.length; i++) {
  //
  //   // Query all content sections inside area
  //   let allSections = contentElements[i].querySelectorAll('h2');
  //
  //     // Loop through every sections inside current content area
  //   for (var y = 0; y < allSections.length; y++) {
  //     sections.push(new Section(allSections[y]));
  //     naviItems.push(navigations[i].navigationItems[y]);
  //   }
  //
  //   containers.push(new Area(allContainers[i]));
  // }

  // // Function navigation link highlighting
  // const highlightNavigationItem = () => {
  //   for (var i = 0; i < sections.length; i++) {
  //     if (sections[i].inview && sections[i].offset < 0) {
  //       sections[i].relatedInstance.classList.add('active');
  //     } else {
  //       sections[i].relatedInstance.classList.remove('active');
  //     }
  //   }
  // }
  //
  // window.addEventListener("scroll", highlightNavigationItem());
  // window.addEventListener("load", highlightNavigationItem());

}

module.exports = toc;
