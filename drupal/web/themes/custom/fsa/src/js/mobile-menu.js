function mobileMenu(menuButtonElements, navigationElement, siteElement) {
  const root = document.documentElement;

  const toggleNavigation = (navigation, siteElement, root) => {
    navigation.classList.toggle("is-open");
    siteElement.classList.toggle("is-moved");
    root.classList.toggle("is-fixed");
  }

  for (let i = 0; i < menuButtonElements.length; i++) {
    let menuButtonElement = menuButtonElements[i];

    // Add scroll listener
    menuButtonElement.addEventListener("click", function(){
      toggleNavigation(navigationElement, siteElement, root);
    });
  }

}

module.exports = mobileMenu;
