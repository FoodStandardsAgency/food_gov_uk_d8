function navigationKeyboard (actionItems, settings) {
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
      prev: function (item, selector, recursive) {
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

  var _active = false
  const isActive = (setActive) => {
    if (typeof setActive == 'undefined') {
      return _active
    }
    else {
      _active = setActive
    }
  }

  const init = () => {
    // Apply keyboard listener to all action items.
    actionItems.forEach((element) => {
      const menuItemAction = element

      const keyDownHandler = (event) => {
        // No arrow navigation in mobile mode.
        if (!isActive()) {
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

          case keyboard.ESCAPE:
            listItem = queryParents(item, settings.listItemSelector)
            var linkElement = listItem.querySelectorAll(settings.menuItemActionSelector)
            toggleEvent = new CustomEvent('navigation:close')

            const top = traversing.top.topItem(listItem);
            top.querySelector('a').dispatchEvent(toggleEvent);
            console.log(top);
            console.log(top.querySelector('a'));
            //console.log(linkElement);
            //console.log(listItem);
            //console.log(settings);
            break;
        };
      }

      // Add event listener to the menu item link.
      menuItemAction.addEventListener('keydown', (e) => {
        keyDownHandler(e)
      })
    })
  }

  return {
    init: init,
    isActive: isActive,
    traversing: traversing,
    queryParents: queryParents
  }
}

module.exports = navigationKeyboard
