import doScrolling from './scrollToElement';

function tableOfContents(tableOfContents, contentElement) {
  // Area class
  // class Area {
  //   constructor(element) {
  //     this.element = element;
  //     this._relatedInstance;
  //   }
  //
  //   set relatedInstance(item) {
  //     this._relatedInstance = item;
  //   }
  //
  //   get relatedInstance() {
  //     return this._relatedInstance;
  //   }
  //
  //   get thisElement() {
  //     return this.element;
  //   }
  //
  //   get offset() {
  //     return this.calcOffset();
  //   }
  //
  //   calcOffset() {
  //     return this.element.getBoundingClientRect().top;
  //   }
  //
  //   get inview() {
  //     return this.calcInview();
  //   }
  //
  //   calcInview() {
  //     var rect = this.element.getBoundingClientRect();
  //     return (
  //         rect.top - window.innerHeight <= 0 &&
  //         rect.bottom >= 0
  //     );
  //   }
  //
  //   get isBottom() {
  //     return this.calcBottom();
  //   }
  //
  //   calcBottom() {
  //     var navigationHeight = this._relatedInstance.offsetHeight;
  //     return this.element.getBoundingClientRect().bottom <= navigationHeight;
  //   }
  //
  // }
  //
  // // Content section class
  // class Section extends Area {
  //
  // }
  //
  // // Navigation class
  // class Navigation {
  //   constructor(element, relatedInstance) {
  //     this.element = element;
  //     this._navigationItems = [];
  //   }
  //
  //   get navigationItems() {
  //     return this._navigationItems;
  //   }
  //
  //   set navigationItems(item) {
  //     var all = this._navigationItems;
  //     all.push(item);
  //   }
  // }
  //
  // // Init instance arrays
  // const navigations = [],
  // navigationItems = [];

  //
  // // Check content area exists
  // if (document.querySelectorAll('.content-area')) {
  //   // Query all content areas
  //   var allContentAreas = document.querySelectorAll('.content-area');
  //   var menuButtons = [];
  //
  //   for (var l = 0; l < allContentAreas.length; l++) {
  //
  //     // Create a navigation element
  //     let navigation = document.createElement("div");
  //
  //     // Create a instance of the navigation
  //     navigations.push(new Navigation(navigation));
  //
  //     navigation.classList.add("content-area__navigation");
  //
  //     // Create a navigation menu button
  //     let menuButton = document.createElement("div");
  //     menuButton.classList.add("content-area__menu-button");
  //
  //     // Create a navigation menu button content
  //     let menuButtonContent = document.createElement("div");
  //     menuButtonContent.classList.add("content-area__menu-button-bar");
  //     menuButton.appendChild(menuButtonContent);
  //
  //     // Add click listener to menu button
  //     menuButton.addEventListener("click", function(e) {
  //       e.preventDefault();
  //       this.classList.toggle('is-open');
  //     });
  //
  //     // Push current menu button to menuButtons array
  //     menuButtons.push(menuButton);
  //
  //     // Get container of the area
  //     let currentContainer = allContentAreas[l].querySelector('.content-area__container');
  //
  //     // Get all the sections inside the container
  //     let containerChildrenArray = currentContainer.children;
  //
  //     // Loop through all the sections
  //     for (var i = 0; i < containerChildrenArray.length; i++) {
  //
  //       let firstHeading;
  //
  //       // Find the title of the section
  //       if (containerChildrenArray[i].querySelector('.content-area__section-title')) {
  //         firstHeading = containerChildrenArray[i].querySelector('.content-area__section-title');
  //
  //         // Hide heading
  //         firstHeading.classList.add('visually-hidden');
  //       }
  //
  //       // Trim the title
  //       let firstHeadingID = firstHeading.innerText.replace(/\s+/g, '-').toLowerCase().substring(0,20);
  //
  //       // Set it as a ID for the section
  //       containerChildrenArray[i].setAttribute("id",`${firstHeadingID}`);
  //
  //       // Create navigation link for the section
  //       let navigationLink = document.createElement("button");
  //       navigationLink.innerHTML = firstHeading.innerText;
  //       navigationLink.classList.add(`content-area__navigation-link`);
  //
  //       navigations[l].navigationItems = navigationLink;
  //
  //       // Append it to the navigation
  //       navigation.appendChild(navigationLink);
  //
  //       // Add click events to navigation link
  //       navigationLink.addEventListener('click',
  //         doScrolling.bind(null, firstHeadingID, 1000, 5));
  //
  //       // Current menuButton
  //       let currentMenuButton = menuButtons[l];
  //
  //       navigationLink.addEventListener('click', () => {
  //         currentMenuButton.classList.toggle('is-open');
  //       });
  //     }
  //
  //     // Get a reference to the parent element
  //     let contentArea = currentContainer.parentNode;
  //
  //     // Insert the menu button into the DOM
  //     contentArea.insertBefore(menuButton, currentContainer);
  //
  //     // Insert the navigation into the DOM
  //     contentArea.insertBefore(navigation, currentContainer);
  //   }
  // }
  //
  // const containers = [],
  // sections = [],
  // naviItems = [];
  //
  // // Push all containers into an array
  // for (var i = 0; i < allContainers.length; i++) {
  //
  //   // Query all content sections inside area
  //   let allSections = allContainers[i].querySelectorAll('.content-area__section');
  //
  //   // Loop through every sections inside current content area
  //   for (var y = 0; y < allSections.length; y++) {
  //     sections.push(new Section(allSections[y]));
  //     naviItems.push(navigations[i].navigationItems[y]);
  //   }
  //
  //   containers.push(new Area(allContainers[i]));
  // }
  //
  // // Set related instance for each instance of the container
  // for (var i = 0; i < containers.length; i++) {
  //   containers[i].relatedInstance = navigations[i].element;
  // }
  //
  // // Set related instance for each instance of the section
  // for (var i = 0; i < sections.length; i++) {
  //   sections[i].relatedInstance = naviItems[i];
  // }
  //
  // Function to toggle sticky navigation
  const toggleStickyNavigation = () => {
  //   for (var i = 0; i < containers.length; i++) {
  //
  //     // Check if navigation is bottom of the content area
  //     if (containers[i].isBottom) {
  //       containers[i].relatedInstance.classList.add('bottom');
  //     } else {
  //       containers[i].relatedInstance.classList.remove('bottom');
  //     }
  //
  //     if (containers[i].inview && containers[i].offset < 0) {
  //       containers[i].relatedInstance.classList.add('sticky');
  //     } else {
  //       containers[i].relatedInstance.classList.remove('sticky');
  //     }
  //   }
  }
  //
  // Function navigation link highlighting
  const highlightNavigationItem = () => {
  //   for (var i = 0; i < sections.length; i++) {
  //     if (sections[i].inview && sections[i].offset < 0) {
  //       sections[i].relatedInstance.classList.add('active');
  //     } else {
  //       sections[i].relatedInstance.classList.remove('active');
  //     }
  //   }
  }

  // console.log(tableOfContents);
  // console.log(contentElement);
  //
  // // Add scroll listener
  // window.addEventListener("scroll", toggleStickyNavigation);
  // window.addEventListener("scroll", highlightNavigationItem);
  //
  // // Add load listener
  // window.addEventListener("load", toggleStickyNavigation);
  // window.addEventListener("load", highlightNavigationItem);

}

module.exports = tableOfContents;
