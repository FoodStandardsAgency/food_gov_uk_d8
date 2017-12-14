function navigation() {

  const settings = {
    hoverClass: 'is-open',
    menuSelector: 'ul.navigation__menu',
    groupSelector: 'li.navigation__item.navigation__item--level-2',
    listItemSelector: 'li',
    menuItemActionSelector: '.navigation__item a, .navigation__item button'
  };

  const keyboard = {
    BACKSPACE: 8,
    COMMA: 188,
    DELETE: 46,
    DOWN: 40,
    END: 35,
    ENTER: 13,
    ESCAPE: 27,
    HOME: 36,
    LEFT: 37,
    PAGE_DOWN: 34,
    PAGE_UP: 33,
    PERIOD: 190,
    RIGHT: 39,
    SPACE: 32,
    TAB: 9,
    UP: 38,
  };

  const queryParents = (elem, selector) => {
    // Element.matches() polyfill
    if (!Element.prototype.matches) {
      Element.prototype.matches =
        Element.prototype.matchesSelector ||
        Element.prototype.mozMatchesSelector ||
        Element.prototype.msMatchesSelector ||
        Element.prototype.oMatchesSelector ||
        Element.prototype.webkitMatchesSelector ||
        function(s) {
          var matches = (this.document || this.ownerDocument).querySelectorAll(s),
              i = matches.length;
          while (--i >= 0 && matches.item(i) !== this) {}
          return i > -1;
        };
    }

    // Get the closest matching element
    for ( ; elem && elem !== document; elem = elem.parentNode ) {
      if ( elem.matches( selector ) ) return elem;
    }

    return null;
  };

  const traversing = {
    // Functions for traversing between items and levels.
    prev: function(item) {
      let currentItem;

      if (currentItem = queryParents(item, settings.listItemSelector)) {
        return currentItem.previousElementSibling;
      }
    },
    next: function(item) {
      let currentItem;

      if (currentItem = queryParents(item, settings.listItemSelector)) {
        return currentItem.nextElementSibling;
      }
    },
    out: function(item) {
      let parentItem;

      // If item has parent menu item, query for its parent menu.
      if (parentItem = queryParents(item.parentNode, settings.listItemSelector)) {
        return parentItem;
      }
    },
    in: function(item) {
      let childList;

      // If item has parent menu item, query for its parent menu.
      if (childList = item.querySelector(settings.menuSelector)) {
        let firstItem;

        if (firstItem = childList.querySelector(settings.listItemSelector)) {
          return firstItem;
        }
      }
    },

    // Functions for traversing between groups.
    group: {
      prev: function(item) {
        let currentGroup = queryParents(item, settings.groupSelector);
        if (currentGroup) {
          return currentGroup.previousElementSibling;
        }

        return null;
      },
      next: function(item) {
        let currentGroup = queryParents(item, settings.groupSelector);
        if (currentGroup) {
          return currentGroup.nextElementSibling;
        }

        return null;
      }
    },

    // Functions for traversing between top level items.
    top: {
      topItem: function(item) {
        let parentItem;

        // If item has parent menu item, query for its parent menu.
        if (parentItem = queryParents(item.parentNode, settings.listItemSelector)) {
          return traversing.top.topItem(parentItem);
        }

        // No parent menu item, return current item.
        return item;
      },
      prev: function(item) {
        return traversing.top.topItem(item).previousElementSibling;
      },
      next: function(item) {
        return traversing.top.topItem(item).nextElementSibling;
      }
    },

    focus: function(item) {
      const link = item.querySelector('a');

      if (link) {
        link.focus();
      }
    }
  };

  const toggleSubmenu = (submenu, newState) => {
    if (newState === undefined) {
      newState = !submenu.classList.contains(settings.hoverClass);
    }

    submenu.classList.toggle(settings.hoverClass, newState);
  };

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

  // Add keyboard navigation so the megamenu is easy to use with a keyboard.
  const menuItemActionArray = [...navigationElementArray[0].querySelectorAll(settings.menuItemActionSelector)];
  menuItemActionArray.forEach((element) => {
    const menuItemAction = element;

    const isItemTopLevel = (item) => {
      const currentMenu = queryParents(item, settings.menuSelector);
      const parentMenu = queryParents(currentMenu.parentNode, settings.menuSelector);

      return parentMenu == null;
    }

    const keyDownHandler = (event) => {
      const item = event.target;
      const keycode = event.keyCode;

      // if (item.is("input:focus, select:focus, textarea:focus, button:focus")) {
      //     // if the event target is a form element we should handle keydown normally
      //     return;
      // }
      let group;
      let topLevelItem;
      let prevTopLevelItem;
      let nextTopLevelItem;
      let listItem;
      let siblingItem;
      let firstItem;

      switch (keycode) {
        // Logic for key LEFT:
        // First try and traverse to the previous group.
        // If one doesn't exist (on first group),
        // traverse to the previous top item.
        case keyboard.LEFT:
          listItem = queryParents(item, settings.listItemSelector);

          if (group = traversing.group.prev(listItem)) {
            traversing.focus(group);
            event.preventDefault();
          }
          else if (prevTopLevelItem = traversing.top.prev(listItem)) {
            traversing.focus(prevTopLevelItem);
            event.preventDefault();
          }
          break;

        // Logic for key UP:
        // First try and traverse to the upper item.
        // If there isn't an upper item (on top level),
        // traverse to the previous sibling.
        case keyboard.UP:
          listItem = queryParents(item, settings.listItemSelector);
          let upperItem;

          if (upperItem = traversing.out(listItem)) {
            traversing.focus(upperItem);
            event.preventDefault();
            break;
          }

          if (siblingItem = traversing.prev(item)) {
            traversing.focus(siblingItem);
            event.preventDefault();
            break;
          }

          // if (topLevelItem = traversing.top.topItem(item)) {
          //   traversing.focus(topLevelItem);

          //   listItem = queryParents(topLevelItem, settings.listItemSelector);
          //   // Close submenu.
          //   const submenu = listItem.querySelector(settings.menuSelector);
          //   toggleSubmenu(submenu, false);
          //   event.preventDefault();
          // }

          break;

        // Logic for key RIGHT:
        // First try and traverse to the next group.
        // If one doesn't exist (on last group),
        // traverse to next top item.
        case keyboard.RIGHT:
          listItem = queryParents(item, settings.listItemSelector);

          if (group = traversing.group.next(listItem)) {
            traversing.focus(group);
            event.preventDefault();
          }
          else if (nextTopLevelItem = traversing.top.next(listItem)) {
            traversing.focus(nextTopLevelItem);
            event.preventDefault();
          }
          break;

        // Logic for key DOWN:
        // First try and jump in the list item's sublist.
        // Otherwise traverse to the next sibling.
        case keyboard.DOWN:
          listItem = queryParents(item, settings.listItemSelector);
          let innerItem;

          if (innerItem = traversing.in(listItem)) {
            // TODO: Open megamenu if needed.

            traversing.focus(innerItem);
            event.preventDefault();
            break;
          }

          // if (isItemTopLevel(item)) {
          //   // TODO: Open submenu.

          //   // Traverse inside.
          //   listItem = queryParents(item, settings.listItemSelector);
          //   let firstSubItem = traversing.in(listItem);

          //   // Focus on first link.
          //   traversing.focus(firstSubItem);

          //   event.preventDefault();
          //   break;
          // }

          if (siblingItem = traversing.next(item)) {
            traversing.focus(siblingItem);
            event.preventDefault();
            break;
          }

          break;
      };
    };

    // Add event listener to the menu item link.
    menuItemAction.addEventListener('keydown', (e) => {
      keyDownHandler(e);
    })
  });
}

module.exports = navigation;
