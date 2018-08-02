import tabbable from 'tabbable'
import debounce from '../../helper/debounce'
import checkMediaQuery from '../../helper/checkMediaQuery'
import breakpoints from '../../helper/breakpoints'
import closestParent from '../../helper/closestParent'
import state from '../../helper/toggleHelpers'

function navigation () {
  const settings = {
    hoverClass: 'is-open',
    mobileDrawerSelector: '.navigation-wrapper__content',
    menuSelector: 'ul.navigation__menu',
    groupSelector: 'li.navigation__item.navigation__item--level-2',
    listItemSelector: 'li.navigation__item--level-1, li.navigation__item--level-3',
    linkElementSelector: '.navigation__link',
    menuItemActionSelector: 'li.navigation__item--level-1 .navigation__link--level-1, li.navigation__item--level-3 .navigation__link--level-3'
  }

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
    UP: 38
  }

  const queryParents = (elem, selector) => {
    // Get the closest matching element
    for (; elem && elem !== document; elem = elem.parentNode) {
      if (elem.matches(selector)) return elem
    }

    return null
  }

  const traversing = {
    // Functions for traversing between items and levels.
    prev: function (item) {
      let currentItem = queryParents(item, settings.listItemSelector)

      if (currentItem && currentItem.previousElementSibling && currentItem.previousElementSibling.matches(settings.listItemSelector)) {
        return currentItem.previousElementSibling
      }
    },
    next: function (item) {
      let currentItem = queryParents(item, settings.listItemSelector)

      if (currentItem && currentItem.nextElementSibling && currentItem.nextElementSibling.matches(settings.listItemSelector)) {
        return currentItem.nextElementSibling
      }
    },
    out: function (item) {
      let parentItem = queryParents(item.parentNode, settings.listItemSelector)

      // If item has parent menu item, query for its parent menu.
      if (parentItem) {
        return parentItem
      }
    },
    in: function (item) {
      let childList = item.querySelector(settings.menuSelector)

      // If item has a child list, return its first item.
      if (childList) {
        let firstItem = childList.querySelector(settings.listItemSelector)

        if (firstItem) {
          return firstItem
        }
      }
    },
    getLevel: function (item) {
      let itemLevel = item.getAttribute('data-menu-level')

      if (itemLevel) {
        return parseInt(itemLevel)
      }

      return false
    },

    // Functions for traversing between groups.
    group: {
      prev: function (item) {
        let currentGroup = queryParents(item, settings.groupSelector)
        if (currentGroup) {
          return traversing.siblings.prev(currentGroup, settings.groupSelector)
        }

        return null
      },
      next: function (item) {
        let currentGroup = queryParents(item, settings.groupSelector)
        if (currentGroup) {
          return traversing.siblings.next(currentGroup, settings.groupSelector)
        }

        return null
      }
    },

    // Functions for traversing between top level items.
    top: {
      topItem: function (item) {
        let parentItem = queryParents(item.parentNode, settings.listItemSelector)

        // If item has parent menu item, query for its parent menu.
        if (parentItem) {
          return traversing.top.topItem(parentItem)
        }

        // No parent menu item, return current item.
        return item
      },
      prev: function (item) {
        return traversing.top.topItem(item).previousElementSibling
      },
      next: function (item) {
        return traversing.top.topItem(item).nextElementSibling
      }
    },

    focus: function (item) {
      const link = item.querySelector(settings.menuItemActionSelector)

      if (link) {
        link.focus()
        return link
      }

      return null
    },

    siblings: {
      prev: function(item, selector, recursive) {
        if (item && item.previousElementSibling && item.previousElementSibling.matches(selector)) {
          return item.previousElementSibling
        }
        else if (recursive && item && item.previousElementSibling) {
          return traversing.siblings.prev(item.previousElementSibling, selector, recursive)
        }
      },
      next: function (item, selector, recursive) {
        if (item && item.nextElementSibling && item.nextElementSibling.matches(selector)) {
          return item.nextElementSibling
        }
        else if (recursive && item && item.nextElementSibling) {
          return traversing.siblings.next(item.nextElementSibling, selector, recursive)
        }
      }
    }
  }

  // Query menu buttons
  const menuButtonOpenElement = document.querySelector('.js-menu-button-open')
  const menuButtonCloseElement = document.querySelector('.js-menu-button-close')

  // Query navigation
  const navigationElementArray = [...document.querySelectorAll('.js-navigation')]

  // Query nav items with child
  const navigationParentItemsArray = [...document.querySelectorAll('.js-nav-item-with-child')]

  // Query nav item togglers
  const navigationItemTogglersArray = [...document.querySelectorAll('.js-nav-item-toggler')]

  // Query back links
  const navigationBackLinksArray = [...document.querySelectorAll('.js-nav-back-link')]

  // Query nav menus
  const navigationMenuElementsArray = [...document.querySelectorAll('.js-nav-menu')]

  // Query main element
  const siteElementArray = [...document.querySelectorAll('.js-site')]

  // Html element
  const root = document.documentElement

  // Tabbable elements inside of navigation
  const tabbableNavigationItems = tabbable(navigationElementArray[0])

  // Mobile navigation object
  const mobileNavigation = {
    on: () => {
      state.on({ element: menuButtonOpenElement, type: 'button' }, 'is-open')
      state.on({ element: menuButtonCloseElement, type: 'button' }, 'is-open')
      state.on({ element: navigationElementArray[0], type: 'content' }, 'is-open', true)

      firstLevelLinkArray.forEach((element) => {
        var closeEvent = new CustomEvent('navigation:close')
        element.dispatchEvent(closeEvent)
      })

      siteElementArray[0].classList.add('is-moved')
      root.classList.add('is-fixed')
    },
    off: () => {
      state.off({ element: menuButtonOpenElement, type: 'button' }, 'is-open')
      state.off({ element: menuButtonCloseElement, type: 'button' }, 'is-open')
      state.off({ element: navigationElementArray[0], type: 'content' }, 'is-open', true)
      siteElementArray[0].classList.remove('is-moved')
      root.classList.remove('is-fixed')
    }
  }

  const navigationMode = {
    isMobile: null,
    windowWidth: null,
    setMode: (isMobile) => {
      var currentState = navigationMode.isMobile
      navigationMode.isMobile = isMobile

      // Only initialize nav if mode has changed.
      if (currentState !== navigationMode.isMobile) {
        initializeNav()
      }
    },
    getMode: () => {
      return navigationMode.isMobile
    },
    updateMode: () => {
      // Refresh
      navigationMode.windowWidth = window.innerWidth
      navigationMode.setMode(checkMediaQuery() === breakpoints.xsmall)

      return navigationMode.getMode()
    }
  }

  // Add keyboard navigation so the megamenu is easy to use with a keyboard.
  const menuItemActionArray = [...navigationElementArray[0].querySelectorAll(settings.menuItemActionSelector)]
  menuItemActionArray.forEach((element) => {
    const menuItemAction = element

    const keyDownHandler = (event) => {
      // No arrow navigation in mobile mode.
      if (navigationMode.getMode()) {
        return;
      }

      const item = event.target
      const keycode = event.keyCode

      let group
      let itemLevel
      let prevTopLevelItem
      let nextTopLevelItem
      let listItem
      let siblingItem

      switch (keycode) {
        // Logic for key LEFT:
        // 1. Try and traverse to the previous group.
        // OR:
        // 2. If one doesn't exist (on first group),
        // traverse to the previous top item.
        case keyboard.LEFT:
          event.preventDefault()

          listItem = queryParents(item, settings.listItemSelector)
          group = traversing.group.prev(listItem)

          // 1. Traverse to the previous group.
          if (group) {
            traversing.focus(group)
            break
          }

          // 2. Traverse to the previous top item.
          prevTopLevelItem = traversing.top.prev(listItem)
          if (prevTopLevelItem) {
            linkElement = prevTopLevelItem.querySelector(settings.linkElementSelector)
            var toggleEvent = new CustomEvent('navigation:open')
            linkElement.dispatchEvent(toggleEvent)

            traversing.focus(prevTopLevelItem)

            break
          }

          break

        // Logic for key UP:
        // 1. If focus is inside third level or deeper,
        // traverse to previous sibling.
        // OR:
        // 2. If no sibling, try and traverse to the outer level.
        case keyboard.UP:
          event.preventDefault()

          listItem = queryParents(item, settings.listItemSelector)
          itemLevel = traversing.getLevel(listItem)
          let upperItem

          // 1. If item level is over 2, traverse between siblings first.
          if (itemLevel > 2 && (siblingItem = traversing.prev(item))) {
            traversing.focus(siblingItem)
            break
          }

          // 2. Traverse out to the upper level.
          upperItem = traversing.out(listItem)
          if (upperItem) {
            traversing.focus(upperItem)
          }

          // 3. If item is already top level, close submenu.
          if (itemLevel == 1) {
            var linkElement = listItem.querySelector(settings.menuItemActionSelector)
            var toggleEvent = new CustomEvent('navigation:close')

            linkElement.dispatchEvent(toggleEvent)
          }

          break

        // Logic for key RIGHT:
        // 1. Try and traverse to the next group.
        // OR:
        // 2. If one doesn't exist (on last group),
        // traverse to next top item.
        case keyboard.RIGHT:
          event.preventDefault()

          listItem = queryParents(item, settings.listItemSelector)

          // 1. Traverse to the next group.
          group = traversing.group.next(listItem)
          if (group) {
            traversing.focus(group)
            break
          }

          // 2. Traverse to the next top item.
          nextTopLevelItem = traversing.top.next(listItem)
          if (nextTopLevelItem) {
            linkElement = nextTopLevelItem.querySelector(settings.linkElementSelector)
            var toggleEvent = new CustomEvent('navigation:open')
            linkElement.dispatchEvent(toggleEvent)

            traversing.focus(nextTopLevelItem)
            break
          }

          break

        // Logic for key DOWN:
        // 1. Try and jump in the list item's child list.
        // OR:
        // 2. Traverse to the next sibling if there's no child list.
        // OR:
        // 3. If there's no sibling, traverse to next group.
        case keyboard.DOWN:
          event.preventDefault()

          listItem = queryParents(item, settings.listItemSelector)
          itemLevel = traversing.getLevel(listItem)
          let innerItem = traversing.in(listItem)

          // 1. Try and traverse into the list item's child list.
          if (innerItem) {
            // Open megamenu first.
            if (itemLevel == 1) {
              var linkElement = listItem.querySelector(settings.menuItemActionSelector)
              var openEvent = new CustomEvent('navigation:open');
              linkElement.dispatchEvent(openEvent);
            }

            traversing.focus(innerItem)
            break
          }

          // 2. Traverse to the next sibling.
          siblingItem = traversing.next(item)
          if (siblingItem) {
            traversing.focus(siblingItem)
            break
          }

          // 3. Traverse to the next group.
          group = traversing.group.next(listItem)
          if (group) {
            traversing.focus(group)
            break
          }

          break

          // Logic for key SPACE:
          // Toggle (open if closed, close if opened) first level items.
          case keyboard.SPACE:
            listItem = queryParents(item, settings.listItemSelector)

            if (traversing.getLevel(listItem) == 1) {
              var linkElement = listItem.querySelector(settings.menuItemActionSelector)
              var toggleEvent = null

              if ([...linkElement.classList].indexOf('is-open') !== -1) {
                toggleEvent = new CustomEvent('navigation:close')
              }
              else {
                toggleEvent = new CustomEvent('navigation:open')
              }

              linkElement.dispatchEvent(toggleEvent)
              event.preventDefault()
            }
            break;

          break;
      };
    }

    // Add event listener to the menu item link.
    menuItemAction.addEventListener('keydown', (e) => {
      keyDownHandler(e)
    })
  })

  // Check everything found
  if (menuButtonOpenElement.length <= 0 ||
    menuButtonCloseElement.length <= 0 ||
    navigationElementArray.length <= 0 ||
    siteElementArray.length <= 0) {
    return console.warn('Navigation elements not found')
  }

  let secondLevelMenuArray = []

  navigationMenuElementsArray.forEach((element) => {
    if ([...element.classList].indexOf('navigation__menu--level-2') !== -1) {
      secondLevelMenuArray = [...secondLevelMenuArray, element]
    }
  })

  let thirdLevelMenuArray = []

  navigationMenuElementsArray.forEach((element) => {
    if ([...element.classList].indexOf('navigation__menu--level-3') !== -1) {
      thirdLevelMenuArray = [...thirdLevelMenuArray, element]
    }
  })

  let firstLevelLinkArray = []

  navigationParentItemsArray.forEach((element) => {
    if ([...element.classList].indexOf('navigation__link--level-1') !== -1) {
      firstLevelLinkArray = [...firstLevelLinkArray, element]
    }
  })

  let secondLevelLinkArray = []

  navigationParentItemsArray.forEach((element) => {
    if ([...element.classList].indexOf('navigation__link--level-2') !== -1) {
      secondLevelLinkArray = [...secondLevelLinkArray, element]
    }
  })

  function initializeListeners () {
    // Add click listener for menu button
    menuButtonOpenElement.addEventListener('click', function (e) {
      mobileNavigation.on()
      menuButtonCloseElement.focus()
    })

    // Add click listener for menu button
    menuButtonCloseElement.addEventListener('click', function (e) {
      mobileNavigation.off()
      menuButtonOpenElement.focus()
    })

    // Item togglers for screen readers.
    navigationItemTogglersArray.forEach((element) => {
      // Content element
      const content = traversing.siblings.next(element, '.navigation__menu', true)
      const linkElement = traversing.siblings.next(element, '.js-nav-item-with-child', true)

      // Handle toggler button click as a proxy element for the actual
      // menu link item. Only expand/collapse via custom events.
      element.addEventListener('click', function (e) {
        var toggleEvent = null

        if ([...linkElement.classList].indexOf('is-open') !== -1) {
          toggleEvent = new CustomEvent('navigation:close')
        }
        else {
          toggleEvent = new CustomEvent('navigation:open')
        }

        linkElement.dispatchEvent(toggleEvent)
      })
    })

    // Items with children. Multipurpose link elements.
    navigationParentItemsArray.forEach((element) => {
      // Content element
      const content = traversing.siblings.next(element, '.navigation__menu', true)

      // Toggler button
      const togglerElement = traversing.siblings.prev(element, '.js-nav-item-toggler', true)

      // Add custom event listener for closing a navigation tree.
      element.addEventListener('navigation:close', function (e) {
        state.toggle(element, content, 'is-open', false, true)

        // Match toggler element state if exists.
        if (togglerElement) {
          state.match(togglerElement, element, 'is-open')
        }
      })

      // Add custom event listener for opening a navigation tree.
      element.addEventListener('navigation:open', function (e) {
        if (!navigationMode.getMode()) {
          // Set all first level items as closed.
          firstLevelLinkArray.forEach((element) => {
            var toggleEvent = new CustomEvent('navigation:close')
            element.dispatchEvent(toggleEvent);
          })
        }

        state.toggle(element, content, 'is-open', true, true)

        // Match toggler element state if exists.
        if (togglerElement) {
          state.match(togglerElement, element, 'is-open')
        }

        // Mobile mode specifics when opening a navigation tree.
        if (navigationMode.getMode()) {
          // Close inner items to inert them.
          content.querySelectorAll('.navigation__link').forEach((element) => {
            var toggleEvent = new CustomEvent('navigation:close')
            element.dispatchEvent(toggleEvent);
          })

          // Focus on first child item and add class for styling reasons.
          content.children[0].children[0].focus()
          navigationElementArray[0].classList.add('has-open-submenu')
        }
        else {
          // Make any toggle buttons inert.
          content.querySelectorAll('button.navigation__link').forEach((element) => {
            element.inert = true
          })
        }
      })

      // Add click listener
      element.addEventListener('click', function (e) {
        if (navigationMode.getMode()) {
          e.preventDefault()

          var openEvent = new CustomEvent('navigation:open')
          element.dispatchEvent(openEvent)
        }
        else {
          // If first level item isn't open when clicked, prevent default
          // and open instead. This is a common hover workaround for touch.
          if ([...element.classList].indexOf('navigation__link--level-1') !== -1 && [...content.classList].indexOf('is-open') === -1) {
            e.preventDefault()

            var toggleEvent = new CustomEvent('navigation:open')
            element.dispatchEvent(toggleEvent);
          }
        }
      })

      // Add a focus listener
      element.addEventListener('focus', function (e) {
        // Special focus handling in mobile navigation mode.
        if (navigationMode.getMode()) {
          // Close all tree if focused on first level item.
          if ([...element.classList].indexOf('navigation__link--level-1') !== -1) {
            firstLevelLinkArray.forEach((element) => {
              var toggleEvent = new CustomEvent('navigation:close')
              element.dispatchEvent(toggleEvent);
            })
          }

          // Close all second level trees if focused on second level item.
          if ([...element.classList].indexOf('navigation__link--level-2') !== -1) {
            secondLevelLinkArray.forEach((element) => {
              var toggleEvent = new CustomEvent('navigation:close')
              element.dispatchEvent(toggleEvent);
            })
          }
        }
      }, true)

      // Add a mouseenter listener (hover).
      // Hover has to be handled in JS, because inert messes with CSS hover.
      element.addEventListener('mouseenter', function (e) {
        if (!navigationMode.getMode()) {
          if ([...element.classList].indexOf('navigation__link--level-1') !== -1) {
            var toggleEvent = new CustomEvent('navigation:open')
            element.dispatchEvent(toggleEvent)
          }
        }
      }, true)
    })

    // Close nav element when mouse leaves navigation element
    // or enters another first level element (relatedTarget).
    // This includes items without children!
    navigationElementArray.forEach((element) => {
      element.addEventListener('mouseout', function (e) {
        if (!navigationMode.getMode()) {
          if (!element.contains(e.relatedTarget) || [...e.relatedTarget.classList].indexOf('navigation__link--level-1') !== -1) {
            firstLevelLinkArray.forEach((element) => {
              if (e.relatedTarget != element) {
                var toggleEvent = new CustomEvent('navigation:close')
                element.dispatchEvent(toggleEvent)
              }
            })
          }
        }
      }, true)
    })

    // Back link
    navigationBackLinksArray.forEach((element) => {
      // Add click listener
      element.addEventListener('click', function (e) {
        if (checkMediaQuery() === breakpoints.xsmall) {
          e.preventDefault()
          state.off({element: element, type: 'button'}, 'is-open')
          state.off({element: closestParent(element, 'js-nav-menu'), type: 'content'}, 'is-open')
          state.off({element: closestParent(element, 'js-nav-menu').previousElementSibling, type: 'button'}, 'is-open')
          closestParent(element, 'js-nav-menu').previousElementSibling.focus()

          if ([...closestParent(element, 'js-nav-menu').classList].indexOf('navigation__menu--level-2') !== -1) {
            navigationElementArray[0].classList.remove('has-open-submenu')
          }
        }
      })
    })

    // Close navigation/subnavigation when focused outside of navigation
    tabbableNavigationItems.forEach((element) => {
      element.addEventListener('blur', function (e) {
        // Close first level items when focusing outside them.
        if (e.relatedTarget === null || (!e.relatedTarget.classList.contains('js-nav-item-with-child') && e.relatedTarget.classList.contains('navigation__link--level-1')) || queryParents(e.relatedTarget, settings.menuSelector) === null) {
          firstLevelLinkArray.forEach((element) => {
            var toggleEvent = new CustomEvent('navigation:close')
            element.dispatchEvent(toggleEvent)
          })
        }

        // Close mobile navigation when focusing outside it.
        if (e.relatedTarget === null || queryParents(e.relatedTarget, settings.mobileDrawerSelector) === null) {
          // Close mobile nav if in mobile mode.
          if (navigationMode.getMode()) {
            mobileNavigation.off()
          }
        }
      })
    })
  }

  // Initialize navigation
  function initializeNav () {
    siteElementArray[0].classList.remove('is-moved')
    root.classList.remove('is-fixed')
    navigationElementArray[0].classList.remove('has-open-submenu')

    // Set initial states
    if (navigationMode.getMode()) {
      state.off({element: menuButtonOpenElement, type: 'button'}, 'is-open')
      state.off({element: menuButtonCloseElement, type: 'button'}, 'is-open')
      state.off({element: navigationElementArray[0], type: 'content'}, 'is-open')

      thirdLevelMenuArray.forEach((element) => {
        element.querySelectorAll('.navigation__link').forEach((element) => {
          // Add tabindex
          element.setAttribute('tabindex', '0')
        })
      })

      navigationParentItemsArray.forEach((element) => {
        // Add tabindex
        element.setAttribute('tabindex', '0')

        // Close all first level items.
        if (element.classList.contains('navigation__link--level-1')) {
          var closeEvent = new CustomEvent('navigation:close')
          element.dispatchEvent(closeEvent)
        }

        // Enable second level buttons, because they function in
        // mobile mode.
        if (element.classList.contains('navigation__link--level-2')) {
          element.inert = false
        }
      })
    } else {
      // Remove mobile navigation states
      state.remove({element: menuButtonOpenElement, type: 'button'}, 'is-open')
      state.remove({element: menuButtonCloseElement, type: 'button'}, 'is-open')
      state.remove({element: navigationElementArray[0], type: 'content'}, 'is-open')

      // Close link items with children
      navigationParentItemsArray.forEach((element) => {
        // Add tabindex
        element.setAttribute('tabindex', '0')

        // Close all first level items.
        if (element.classList.contains('navigation__link--level-1')) {
          var closeEvent = new CustomEvent('navigation:close')
          element.dispatchEvent(closeEvent)
        }

        // Disable second level buttons which have a function in mobile mode,
        // but not in full mode. This fixes semantics for assistive tech.
        if (element.classList.contains('navigation__link--level-2')) {
          element.inert = true
        }
      })

      // Set state off from second subnavigation
      secondLevelMenuArray.forEach((element) => {
        state.remove({ element: element, type: 'content' }, 'is-open')
      })

      // Set state off from third subnavigation
      thirdLevelMenuArray.forEach((element) => {
        state.remove({element: element, type: 'content'}, 'is-open')
      })
    }

    // Set state off from back links
    navigationBackLinksArray.forEach((element) => {
      state.off({element: element, type: 'button'}, 'is-open')
    })
  }

  const resizeHandler = debounce(function () {
    // Check if vertical resizing
    if (window.innerWidth === navigationMode.windowWidth) {
      return false
    }

    navigationMode.updateMode()
  }, 250)
  window.addEventListener('resize', resizeHandler)

  initializeListeners()
  navigationMode.updateMode()
}

module.exports = navigation
