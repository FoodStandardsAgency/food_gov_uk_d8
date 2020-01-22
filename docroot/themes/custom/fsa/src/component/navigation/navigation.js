import tabbable from 'tabbable'
import debounce from '../../helper/debounce'
import checkMediaQuery from '../../helper/checkMediaQuery'
import breakpoints from '../../helper/breakpoints'
import closestParent from '../../helper/closestParent'
import state from '../../helper/toggleHelpers'
import navigationKeyboard from './navigation-keyboard'

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

  // Navigation items that have an action.
  const menuItemActionArray = [...navigationElementArray[0].querySelectorAll(settings.menuItemActionSelector)]

  // Mobile navigation object with on/off state functions.
  // Toggles whether mobile navigation wrapper is visible and active or not.
  // Note: this does NOT switch between mobile and full navigation modes.
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

  // Navigation mode object which switches between mobile and full navigation.
  // Abstracts breakpoint checking and current mode.
  const navigationMode = {
    isMobile: null,
    windowWidth: null,
    setMode: (isMobile) => {
      var currentState = navigationMode.isMobile
      navigationMode.isMobile = isMobile

      // Only initialize nav if mode has changed.
      if (currentState !== navigationMode.isMobile) {
        initializeNav()
        keyboard.isActive(!navigationMode.isMobile)
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

  // Init keyboard navigation so the megamenu is easy to use with a keyboard.
  const keyboard = navigationKeyboard(menuItemActionArray, settings)
  keyboard.init()


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

    // Add click listener for menu button
    navigationElementArray[0].addEventListener('mobileNavClose', function (e) {
      if (navigationMode.getMode()) {
        mobileNavigation.off()
        menuButtonOpenElement.focus()
      }
    })

    // Item togglers for screen readers.
    navigationItemTogglersArray.forEach((element) => {
      // Content element
      const content = keyboard.traversing.siblings.next(element, '.navigation__menu', true)
      const linkElement = keyboard.traversing.siblings.next(element, '.js-nav-item-with-child', true)

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
      const content = keyboard.traversing.siblings.next(element, '.navigation__menu', true)

      // Toggler button
      const togglerElement = keyboard.traversing.siblings.prev(element, '.js-nav-item-toggler', true)

      // Add custom event listener for closing a navigation tree.
      element.addEventListener('navigation:close', function (e) {
        // Close link and its menu content.
        // Also inert submenu links recursively.
        state.toggle(element, content, 'is-open', false, true)

        // Match toggler element state if exists.
        if (togglerElement) {
          state.match(togglerElement, element, 'is-open')
        }
      })

      // Add custom event listener for opening a navigation tree.
      element.addEventListener('navigation:open', function (e) {
        if (!navigationMode.getMode()) {
          // Close all first level items in full mode before opening.
          firstLevelLinkArray.forEach((element) => {
            var toggleEvent = new CustomEvent('navigation:close')
            element.dispatchEvent(toggleEvent);
          })
        }

        // Open this link and its menu content.
        // Also remove submenu links' inert state recursively.
        state.toggle(element, content, 'is-open', true, true)

        // Match toggler element state if exists.
        if (togglerElement) {
          state.match(togglerElement, element, 'is-open')
        }

        // Mobile mode specifics when opening a navigation tree.
        if (navigationMode.getMode()) {
          // Close inner items to re-inert them.
          [...content.querySelectorAll('.navigation__link')].forEach((element) => {
            var toggleEvent = new CustomEvent('navigation:close')
            element.dispatchEvent(toggleEvent);
          })

          // Focus on first child item and add state class to root element.
          content.children[0].children[0].focus()
          navigationElementArray[0].classList.add('has-open-submenu')
        }
        else {
          // Make inner buttons inert as they are not needed in full mode.
          [...content.querySelectorAll('button.navigation__link')].forEach((element) => {
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

            navigationElementArray[0].classList.remove('has-open-submenu')
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


    const closeTest = function (e, element) {
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
    };
    // Close nav element when mouse leaves navigation element
    // or enters another first level element (relatedTarget).
    // This includes items without children!
    navigationElementArray.forEach((element) => {
      element.addEventListener('mouseout', function (e) { closeTest(e, element); }, true);
      element.addEventListener('focusout', function (e) { closeTest(e, element); }, true);
    });

    // Back link in mobile mode.
    navigationBackLinksArray.forEach((element) => {
      // Add click listener
      element.addEventListener('click', function (e) {
        if (navigationMode.getMode()) {
          e.preventDefault()
          const parentItem = closestParent(element, 'navigation__item')
          const parentLink = parentItem.querySelector('.navigation__link')

          // Focus on the parent link of this nav tree branch.
          // Focus event handling will close the branch.
          parentLink.focus()
        }
      })
    })

    // Close navigation/subnavigation when focused outside of navigation
    tabbableNavigationItems.forEach((element) => {
      element.addEventListener('focusOut', function (e) {
        // Close mobile navigation when focusing outside it.
        if (navigationMode.getMode()) {
          if (e.relatedTarget !== null && keyboard.queryParents(e.relatedTarget, settings.mobileDrawerSelector) === null) {
            mobileNavigation.off()
          }
        }
        else {
          // Close first level items when focusing outside them in full mode.
          if (e.relatedTarget === null || (!e.relatedTarget.classList.contains('js-nav-item-with-child') && e.relatedTarget.classList.contains('navigation__link--level-1')) || keyboard.queryParents(e.relatedTarget, settings.menuSelector) === null) {
            firstLevelLinkArray.forEach((element) => {
              var toggleEvent = new CustomEvent('navigation:close')
              element.dispatchEvent(toggleEvent)
            })
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
      mobileNavigation.off()
    } else {
      // Remove mobile navigation states
      state.remove({element: menuButtonOpenElement, type: 'button'}, 'is-open')
      state.remove({element: menuButtonCloseElement, type: 'button'}, 'is-open')
      state.remove({element: navigationElementArray[0], type: 'content'}, 'is-open')

      // First open all levels recursively to remove all inert state.
      state.on({element: navigationElementArray[0].querySelector('.navigation__menu--level-1'), type: 'content'}, 'is-open', true)

      navigationParentItemsArray.forEach((element) => {
        // Close all levels.
        var closeEvent = new CustomEvent('navigation:close')
        element.dispatchEvent(closeEvent)

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

  // Debounce switching between mobile and full navigation modes when resizing.
  const resizeHandler = debounce(function () {
    // Check if vertical resizing
    if (window.innerWidth === navigationMode.windowWidth) {
      return false
    }

    navigationMode.updateMode()
  }, 250)
  window.addEventListener('resize', resizeHandler)

  // Initialize listeners and set initial navigation mode.
  initializeListeners()
  navigationMode.updateMode()
}

module.exports = navigation
