import debounce from '../../helper/debounce'
import checkMediaQuery from '../../helper/checkMediaQuery'
import breakpoints from '../../helper/breakpoints'
import closestParent from '../../helper/closestParent'
import { setStateOff, setStateOn, removeState, toggleState } from '../../helper/toggleHelpers'

function navigation () {
  const settings = {
    hoverClass: 'is-open',
    menuSelector: 'ul.navigation__menu',
    groupSelector: 'header.navigation__header, li.navigation__item.navigation__item--level-2',
    listItemSelector: 'header.navigation__header, li.navigation__item',
    menuItemActionSelector: '.navigation__item a, .navigation__item button'
  }

  const KEYCODE = {
    ENTER: 13,
    ESC: 27,
    SPACE: 32
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

      if (currentItem) {
        return currentItem.previousElementSibling
      }
    },
    next: function (item) {
      let currentItem = queryParents(item, settings.listItemSelector)

      if (currentItem) {
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
    },

    // Functions for traversing between groups.
    group: {
      prev: function (item) {
        let currentGroup = queryParents(item, settings.groupSelector)
        if (currentGroup) {
          return currentGroup.previousElementSibling
        }

        return null
      },
      next: function (item) {
        let currentGroup = queryParents(item, settings.groupSelector)
        if (currentGroup) {
          return currentGroup.nextElementSibling
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
      const link = item.querySelector('a')

      if (link) {
        link.focus()
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

  // Query back links
  const navigationBackLinksArray = [...document.querySelectorAll('.js-nav-back-link')]

  // Query nav menus
  const navigationMenuElementsArray = [...document.querySelectorAll('.js-nav-menu')]

  // Query main element
  const siteElementArray = [...document.querySelectorAll('.js-site')]

  // Html element
  const root = document.documentElement

  // Add keyboard navigation so the megamenu is easy to use with a keyboard.
  const menuItemActionArray = [...navigationElementArray[0].querySelectorAll(settings.menuItemActionSelector)]
  menuItemActionArray.forEach((element) => {
    const menuItemAction = element

    const keyDownHandler = (event) => {
      const item = event.target
      const keycode = event.keyCode

      let group
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
          listItem = queryParents(item, settings.listItemSelector)
          group = traversing.group.prev(listItem)

          // 1. Traverse to the previous group.
          if (group) {
            traversing.focus(group)
            event.preventDefault()
            break
          }

          // 2. Traverse to the previous top item.
          prevTopLevelItem = traversing.top.prev(listItem)
          if (prevTopLevelItem) {
            traversing.focus(prevTopLevelItem)
            event.preventDefault()
            break
          }

          break

        // Logic for key UP:
        // 1. If focus is inside third level or deeper,
        // traverse to previous sibling.
        // OR:
        // 2. If no sibling, try and traverse to the outer level.
        case keyboard.UP:
          listItem = queryParents(item, settings.listItemSelector)
          let itemLevel = traversing.getLevel(listItem)
          let upperItem

          // 1. If item level is over 2, traverse between siblings first.
          if (itemLevel > 2 && (siblingItem = traversing.prev(item))) {
            traversing.focus(siblingItem)
            event.preventDefault()
            break
          }

          // 2. Traverse out to the upper level.
          upperItem = traversing.out(listItem)
          if (upperItem) {
            traversing.focus(upperItem)
            event.preventDefault()

            // TODO: Ask megamenu to close.
            break
          }

          break

        // Logic for key RIGHT:
        // 1. Try and traverse to the next group.
        // OR:
        // 2. If one doesn't exist (on last group),
        // traverse to next top item.
        case keyboard.RIGHT:
          listItem = queryParents(item, settings.listItemSelector)

          // 1. Traverse to the next group.
          group = traversing.group.next(listItem)
          if (group) {
            traversing.focus(group)
            event.preventDefault()
            break
          }

          // 2. Traverse to the next top item.
          nextTopLevelItem = traversing.top.next(listItem)
          if (nextTopLevelItem) {
            traversing.focus(nextTopLevelItem)
            event.preventDefault()
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
          listItem = queryParents(item, settings.listItemSelector)
          let innerItem = traversing.in(listItem)

          // 1. Try and traverse into the list item's child list.
          if (innerItem) {
            // TODO: Ask megamenu to open first.

            traversing.focus(innerItem)
            event.preventDefault()
            break
          }

          // 2. Traverse to the next sibling.
          siblingItem = traversing.next(item)
          if (siblingItem) {
            traversing.focus(siblingItem)
            event.preventDefault()
            break
          }

          // 3. Traverse to the next group.
          group = traversing.group.next(listItem)
          if (group) {
            traversing.focus(group)
            event.preventDefault()
            break
          }

          break
      };
    }

    // Add event listener to the menu item link.
    menuItemAction.addEventListener('keydown', (e) => {
      keyDownHandler(e)
    })
  })

  /// Mobile navigation

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

  let firstLevelLinkArray = []

  navigationParentItemsArray.forEach((element) => {
    if ([...element.classList].indexOf('navigation__link--level-1') !== -1) {
      firstLevelLinkArray = [...firstLevelLinkArray, element]
    }
  })

  // Add click listener for menu button
  menuButtonOpenElement.addEventListener('click', function (e) {
    setStateOn({element: menuButtonOpenElement, type: 'button'}, 'is-open')
    setStateOn({element: menuButtonCloseElement, type: 'button'}, 'is-open')
    setStateOn({element: navigationElementArray[0], type: 'content'}, 'is-open')
    siteElementArray[0].classList.add('is-moved')
    root.classList.add('is-fixed')
    menuButtonCloseElement.focus()
  })

  // Add click listener for menu button
  menuButtonCloseElement.addEventListener('click', function (e) {
    setStateOff({element: menuButtonOpenElement, type: 'button'}, 'is-open')
    setStateOff({element: menuButtonCloseElement, type: 'button'}, 'is-open')
    setStateOff({element: navigationElementArray[0], type: 'content'}, 'is-open')
    siteElementArray[0].classList.remove('is-moved')
    root.classList.remove('is-fixed')
    menuButtonOpenElement.focus()
  })

  // Items with children
  navigationParentItemsArray.forEach((element) => {
    // Add tabindex
    element.setAttribute('tabindex', '0')

    // Add click listener
    element.addEventListener('click', function (e) {
      if (checkMediaQuery() === breakpoints.xsmall) {
        e.preventDefault()
        setStateOn({element: element, type: 'button'}, 'is-open')
        setStateOn({element: element.nextElementSibling, type: 'content'}, 'is-open')
        element.nextElementSibling.children[0].children[0].focus()
        navigationElementArray[0].classList.add('has-open-submenu')
      }
    })
  })

  // Back link
  navigationBackLinksArray.forEach((element) => {
    // Add click listener
    element.addEventListener('click', function (e) {
      if (checkMediaQuery() === breakpoints.xsmall) {
        e.preventDefault()
        setStateOff({element: element, type: 'button'}, 'is-open')
        setStateOff({element: closestParent(element, 'js-nav-menu'), type: 'content'}, 'is-open')
        setStateOff({element: closestParent(element, 'js-nav-menu').previousElementSibling, type: 'button'}, 'is-open')
        closestParent(element, 'js-nav-menu').previousElementSibling.focus()

        if ([...closestParent(element, 'js-nav-menu').classList].indexOf('navigation__menu--level-2') !== -1) {
          navigationElementArray[0].classList.remove('has-open-submenu')
        }
      }
    })
  })

  // Initialize navigation
  function initializeNav () {
    siteElementArray[0].classList.remove('is-moved')
    root.classList.remove('is-fixed')
    navigationElementArray[0].classList.remove('has-open-submenu')

    // Set initial states
    if (checkMediaQuery() === breakpoints.xsmall) {
      setStateOff({element: menuButtonOpenElement, type: 'button'}, 'is-open')
      setStateOff({element: menuButtonCloseElement, type: 'button'}, 'is-open')
      setStateOff({element: navigationElementArray[0], type: 'content'}, 'is-open')

      navigationParentItemsArray.forEach((element) => {
        setStateOff({element: element, type: 'button'}, 'is-open')
      })

      navigationMenuElementsArray.forEach((element) => {
        setStateOff({element: element, type: 'content'}, 'is-open')
      })

      navigationBackLinksArray.forEach((element) => {
        setStateOff({element: element, type: 'button'}, 'is-open')
      })
    } else {
      removeState({element: menuButtonOpenElement, type: 'button'}, 'is-open')
      removeState({element: menuButtonCloseElement, type: 'button'}, 'is-open')
      removeState({element: navigationElementArray[0], type: 'content'}, 'is-open')

      navigationParentItemsArray.forEach((element) => {
        removeState({element: element, type: 'button'}, 'is-open')
      })

      navigationMenuElementsArray.forEach((element) => {
        removeState({element: element, type: 'content'}, 'is-open')
      })

      navigationBackLinksArray.forEach((element) => {
        removeState({element: element, type: 'button'}, 'is-open')
      })
    }

    // Add listeners
    firstLevelLinkArray.forEach((element) => {
      // Add a focus listener
      element.addEventListener('focus', function (e) {
        firstLevelLinkArray.forEach((element) => {
          setStateOff({element: element, type: 'button'}, 'is-open')
        })

        secondLevelMenuArray.forEach((element) => {
          setStateOff({element, type: 'content'}, 'is-open')
        })
      }, true)

      // Add a keypress listener
      element.addEventListener('keypress', function (e) {
        const content = element.nextElementSibling

        if (e.which === KEYCODE.SPACE) {
          e.preventDefault()
          toggleState(element, content, 'is-open')
        }
        if (e.which === KEYCODE.ENTER) {
          if ([...content.classList].indexOf('is-open') !== -1) {
            setStateOff({element: element, type: 'button'}, 'is-open')
            setStateOff({element: content, type: 'content'}, 'is-open')
          } else {
            e.preventDefault()
            setStateOn({element: element, type: 'button'}, 'is-open')
            setStateOn({element: content, type: 'content'}, 'is-open')
          }
        }
      })

      // If touch device
      element.addEventListener('touchstart', function addtouchclass (e) {
        if (checkMediaQuery() !== breakpoints.xsmall) {
          const content = element.nextElementSibling
          if ([...content.classList].indexOf('is-open') !== -1) {
            element.classList.remove('is-open')
            content.classList.remove('is-open')
          } else {
            e.preventDefault()
            firstLevelLinkArray.forEach((element) => {
              element.classList.remove('is-open')
            })
            element.classList.add('is-open')
            secondLevelMenuArray.forEach((element) => {
              element.classList.remove('is-open')
            })
            content.classList.add('is-open')
          }
        }
      }, false)

      // Add a mouseenter listener
      element.addEventListener('mouseenter', function (e) {
        firstLevelLinkArray.forEach((element) => {
          removeState({element: element, type: 'button'}, 'is-open')
        })

        secondLevelMenuArray.forEach((element) => {
          removeState({element: element, type: 'content'}, 'is-open')
        })
      }, true)
    })

    // Remove states
    removeState({element: menuButtonOpenElement, type: 'button'}, 'is-open')
    removeState({element: menuButtonCloseElement, type: 'button'}, 'is-open')
    removeState({element: navigationElementArray[0], type: 'content'}, 'is-open')

    secondLevelMenuArray.forEach((element) => {
      setStateOff({element, type: 'content'}, 'is-open')
    })

    navigationBackLinksArray.forEach((element) => {
      removeState({element, type: 'button'}, 'is-open')
    })
  }

  // Current window width
  let windowWidth = window.innerWidth

  const resizeHandler = debounce(function () {
    // Check if vertical resizing
    if (window.innerWidth === windowWidth) {
      return false
    }

    windowWidth = window.innerWidth

    initializeNav()
  }, 250)

  window.addEventListener('resize', resizeHandler)

  initializeNav()
}

module.exports = navigation
