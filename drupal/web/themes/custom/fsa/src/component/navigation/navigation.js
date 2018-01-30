import nextByClass from '../../core/helper/nextByClass';
import debounce from '../../core/helper/debounce';
import checkMediaQuery from '../../core/helper/checkMediaQuery';
import breakpoints from '../../core/helper/breakpoints';
import closestParent from '../../core/helper/closestParent';
import inert from 'wicg-inert';

function navigation() {

  const settings = {
    hoverClass: 'is-open',
    menuSelector: 'ul.navigation__menu',
    groupSelector: 'header.navigation__header, li.navigation__item.navigation__item--level-2',
    listItemSelector: 'header.navigation__header, li.navigation__item',
    menuItemActionSelector: '.navigation__item a, .navigation__item button'
  };

  const KEYCODE = {
    ESC: 27,
    SPACE: 32,
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

      // If item has a child list, return its first item.
      if (childList = item.querySelector(settings.menuSelector)) {
        let firstItem;

        if (firstItem = childList.querySelector(settings.listItemSelector)) {
          return firstItem;
        }
      }
    },
    getLevel: function(item) {
      let itemLevel = item.getAttribute('data-menu-level');

      if (itemLevel) {
        return parseInt(itemLevel);
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

  // Query navigation related elements
  const menuButtonElementsArray = [...document.querySelectorAll('.js-menu-button')];
  const navigationElementArray = [...document.querySelectorAll('.js-navigation')];

  // Query nav items with child
  const navigationParentItemsArray = [...document.querySelectorAll('.js-nav-item-with-child')];

  // Query back links
  const navigationBackLinksArray = [...document.querySelectorAll('.js-nav-back-link')];

  // Query nav menus
  const navigationMenuElementsArray = [...document.querySelectorAll('.js-nav-menu')];

  // Query main element
  const siteElementArray = [...document.querySelectorAll('.js-site')];

  // Html element
  const root = document.documentElement;

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

      let group;
      let topLevelItem;
      let prevTopLevelItem;
      let nextTopLevelItem;
      let listItem;
      let siblingItem;
      let firstItem;

      switch (keycode) {
        // Logic for key LEFT:
        // 1. Try and traverse to the previous group.
        // OR:
        // 2. If one doesn't exist (on first group),
        // traverse to the previous top item.
        case keyboard.LEFT:
          listItem = queryParents(item, settings.listItemSelector);

          // 1. Traverse to the previous group.
          if (group = traversing.group.prev(listItem)) {
            traversing.focus(group);
            event.preventDefault();
            break;
          }

          // 2. Traverse to the previous top item.
          if (prevTopLevelItem = traversing.top.prev(listItem)) {
            traversing.focus(prevTopLevelItem);
            event.preventDefault();
            break;
          }

          break;

        // Logic for key UP:
        // 1. If focus is inside third level or deeper,
        // traverse to previous sibling.
        // OR:
        // 2. If no sibling, try and traverse to the outer level.
        case keyboard.UP:
          listItem = queryParents(item, settings.listItemSelector);
          let itemLevel = traversing.getLevel(listItem);
          let upperItem;

          // 1. If item level is over 2, traverse between siblings first.
          if (itemLevel > 2 && (siblingItem = traversing.prev(item))) {
            traversing.focus(siblingItem);
            event.preventDefault();
            break;
          }

          // 2. Traverse out to the upper level.
          if (upperItem = traversing.out(listItem)) {
            traversing.focus(upperItem);
            event.preventDefault();

            // TODO: Ask megamenu to close.
            break;
          }

          break;

        // Logic for key RIGHT:
        // 1. Try and traverse to the next group.
        // OR:
        // 2. If one doesn't exist (on last group),
        // traverse to next top item.
        case keyboard.RIGHT:
          listItem = queryParents(item, settings.listItemSelector);

          // 1. Traverse to the next group.
          if (group = traversing.group.next(listItem)) {
            traversing.focus(group);
            event.preventDefault();
            break;
          }

          // 2. Traverse to the next top item.
          if (nextTopLevelItem = traversing.top.next(listItem)) {
            traversing.focus(nextTopLevelItem);
            event.preventDefault();
            break;
          }

          break;

        // Logic for key DOWN:
        // 1. Try and jump in the list item's child list.
        // OR:
        // 2. Traverse to the next sibling if there's no child list.
        // OR:
        // 3. If there's no sibling, traverse to next group.
        case keyboard.DOWN:
          listItem = queryParents(item, settings.listItemSelector);
          let innerItem;

          // 1. Try and traverse into the list item's child list.
          if (innerItem = traversing.in(listItem)) {
            // TODO: Ask megamenu to open first.

            traversing.focus(innerItem);
            event.preventDefault();
            break;
          }

          // 2. Traverse to the next sibling.
          if (siblingItem = traversing.next(item)) {
            traversing.focus(siblingItem);
            event.preventDefault();
            break;
          }

          // 3. Traverse to the next group.
          if (group = traversing.group.next(listItem)) {
            traversing.focus(group);
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

  /// Mobile navigation

  // Check everything found
  if (menuButtonElementsArray.length <= 0 ||
    navigationElementArray.length <= 0 ||
    siteElementArray.length <= 0) {
    return console.warn('Navigation elements not found');
  }

  function setStateOff(options, elemState) {
    const element = options.element;

    switch (options.type) {
      case 'button':
        element.classList.remove(elemState);
        // element.classList.add('is-closed');
        element.setAttribute('aria-expanded', false);
        break;
      case 'content':
        element.classList.remove(elemState);
        // element.classList.add('is-hidden');
        element.setAttribute('aria-hidden', true);
        element.inert = true;
        break;
      default:
        break;
    }
  }

  function setStateOn(options, elemState) {
    const element = options.element;

    switch (options.type) {
      case 'button':
        // element.classList.remove('is-closed');
        element.classList.add(elemState);
        element.setAttribute('aria-expanded', true);
        break;
      case 'content':
        element.classList.add(elemState);
        // element.classList.remove('is-hidden');
        element.setAttribute('aria-hidden', false);
        element.inert = false;
        break;
      default:
        break;
    }
  }

  function removeState(options, elemState) {
    const element = options.element;

    switch (options.type) {
      case 'button':
        element.classList.remove(elemState);
        element.removeAttribute('aria-expanded');
        break;
      case 'content':
        element.classList.remove(elemState);
        element.removeAttribute('aria-hidden');
        element.inert = false;
        break;
      default:
        break;
    }
  }
  function toggleState(elem, elemRefItem, elemState) {
    if (elemRefItem.classList.contains(elemState)) {
      setStateOff({element: elem, type: 'button'}, elemState);
      setStateOff({element: elemRefItem, type: 'content'}, elemState);
    } else {
      setStateOn({element: elem, type: 'button'}, elemState);
      setStateOn({element: elemRefItem, type: 'content'}, elemState);
    }
  }

  // Loop the menubuttons
  menuButtonElementsArray.forEach((element) => {
    const menuButtonElement = element;

    // Add click listener
    menuButtonElement.addEventListener("click", function(e){
      toggleState(this, navigationElementArray[0], 'is-open');
      siteElementArray[0].classList.toggle("is-moved");
      root.classList.toggle("is-fixed");
    });
  });

  // Items with children
  navigationParentItemsArray.forEach((element) => {
    // Add click listener
    element.addEventListener("click", function(e){
      if (checkMediaQuery() === breakpoints.xsmall) {
        e.preventDefault();
        setStateOn({element: element, type: 'content'}, 'is-open');
        setStateOn({element: element.nextElementSibling, type: 'content'}, 'is-open');
      }
    });
  });

  // Back link
  navigationBackLinksArray.forEach((element) => {
    // Add click listener
    element.addEventListener("click", function(e){
      if (checkMediaQuery() === breakpoints.xsmall) {
        e.preventDefault();
        setStateOff({element: element, type: 'button'}, 'is-open');
        setStateOff({element: closestParent(element, 'js-nav-menu'), type: 'content'}, 'is-open');
      }
    });
  });

  function initializeMobileNav() {
    if (checkMediaQuery() === breakpoints.xsmall) {
      menuButtonElementsArray.forEach((element) => {
        setStateOff({element: element, type: 'button'}, 'is-open');
      });
      setStateOff({element: navigationElementArray[0], type: 'content'}, 'is-open');

      navigationParentItemsArray.forEach((element) => {
        setStateOff({element: element, type: 'button'}, 'is-open');
      });

      navigationMenuElementsArray.forEach((element) => {
        setStateOff({element: element, type: 'content'}, 'is-open');
      });

      navigationBackLinksArray.forEach((element) => {
        setStateOff({element: element, type: 'button'}, 'is-open');
      });
    } else {
      menuButtonElementsArray.forEach((element) => {
        setStateOn({element: element, type: 'button'}, 'is-open');
      });
      
      removeState({element: navigationElementArray[0], type: 'content'}, 'is-open');

      navigationParentItemsArray.forEach((element) => {
        removeState({element: element, type: 'button'}, 'is-open');
      });

      navigationMenuElementsArray.forEach((element) => {
        removeState({element: element, type: 'content'}, 'is-open');
      });

      navigationBackLinksArray.forEach((element) => {
        removeState({element: element, type: 'button'}, 'is-open');
      });
    }
  }

  const resizeHandler = debounce(function() {
    initializeMobileNav();
  }, 250);

  window.addEventListener('resize', resizeHandler);

  initializeMobileNav();
}

module.exports = navigation;
