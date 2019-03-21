
function toggleSidebarDocumentMenu() {

  let sidebarDocumentMenus = document.querySelectorAll('div.document-menu-side-menu');

  let toggleCSSClass = (element, className) => {

    if (element.classList) {
      element.classList.toggle(className);
    }
    else {
      // For IE9
      let classes = element.className.split(" ");
      let i = classes.indexOf(className);

      if (i >= 0)
        classes.splice(i, 1);
      else
        classes.push(className);
      element.className = classes.join(" ");
    }
  };

  sidebarDocumentMenus.forEach((documentMenu) => {
    let heading = documentMenu.querySelector('h2.sidebar-title');
    let nav = documentMenu.querySelector('nav.document-menu');

    heading.onclick = () => {
      toggleCSSClass(heading, 'sidebar-title__open');
      toggleCSSClass(nav, 'document-menu__open');
    }
  });
}

module.exports = toggleSidebarDocumentMenu;
