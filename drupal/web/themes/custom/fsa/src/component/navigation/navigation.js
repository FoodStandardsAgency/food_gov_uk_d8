function navigation() {

  // Query navigation related elements
  const menuButtonElementsArray = [...document.querySelectorAll('.js-menu-button')];
  const navigationElementArray = [...document.querySelectorAll('.js-navigation')];
  
  // Query main element
  const siteElementArray = [...document.querySelectorAll('.js-site')];
  
  // Html element
  const root = document.documentElement;

  // Check everything found
  if (menuButtonElementsArray.length <= 0 ||
    navigationElementArray.length <= 0 ||
    siteElementArray.length <= 0) {
    return console.warn('Navigation elements not found');
  }

  // Toggle states
  const toggleNavigation = (button) => {
    navigationElementArray[0].classList.toggle("is-open");
    siteElementArray[0].classList.toggle("is-moved");
    root.classList.toggle("is-fixed");
  }

  // Loop the menubuttons
  menuButtonElementsArray.forEach((element) => {
    const menuButtonElement = element;

    // Add click listener
    menuButtonElement.addEventListener("click", function(){
      toggleNavigation(this);
    });
  });

  // Toggle states
  const toggleSubmenu = (button, submenu) => {

  }

  // Add 'open submenu button'
  const menuItemWithSubmenuElementArray = [...navigationElementArray[0].querySelectorAll('.menu-item.menu-item--expanded')];

  // Check everything found
  if (menuItemWithSubmenuElementArray <= 0) {
    return;
  }

  // Loop
  menuItemWithSubmenuElementArray.forEach((element) => {
    const menuItemWithSubmenuElement = element;
    const submenuElement = element.children[1];
    const submenuAriaLabel = `Submenu of ${element.children[0].innerHTML}`;

    // Hide by default
    submenuElement.classList.add(`is-closed`);

    const openSubmenuButton = document.createElement('button');
    openSubmenuButton.innerHTML = `Open menu`;
    openSubmenuButton.classList.add(`navigation__submenu-button`);
    openSubmenuButton.setAttribute(`aria-label`, submenuAriaLabel);

    menuItemWithSubmenuElement.appendChild(openSubmenuButton);

    // Add click listener
    menuItemWithSubmenuElement.addEventListener("click", function(){
      toggleSubmenu(this, submenuElement);
    });
  });
}

module.exports = navigation;
