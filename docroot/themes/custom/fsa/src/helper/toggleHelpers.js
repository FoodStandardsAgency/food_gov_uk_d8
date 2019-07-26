const state = {
  on: (options, elemState, recursiveInert) => {
    switch (options.type) {
      case 'button':
        options.element.classList.add(elemState)
        options.element.setAttribute('aria-expanded', 'true')
        break
      case 'content':
        options.element.classList.add(elemState)
        options.element.setAttribute('aria-hidden', 'false')

        if (recursiveInert) {
          [...options.element.querySelectorAll('a, button')].forEach((tabbableChild) => {
            tabbableChild.inert = false
          })
        }
        else {
          options.element.inert = false
        }
        break
      default:
        break
    }
  },

  off: (options, elemState, recursiveInert) => {
    switch (options.type) {
      case 'button':
        options.element.classList.remove(elemState)
        options.element.setAttribute('aria-expanded', 'false')
        break
      case 'content':
        options.element.classList.remove(elemState)
        options.element.setAttribute('aria-hidden', 'true')

        if (recursiveInert) {
          [...options.element.querySelectorAll('a, button')].forEach((tabbableChild) => {
            tabbableChild.inert = true
          })
        }
        else {
          options.element.inert = true
        }
        break
      default:
        break
    }
  },

  toggle: (elem, elemRefItem, elemState, newState, recursiveInert) => {
    if (newState == undefined) {
      newState = !elemRefItem.classList.contains(elemState)
    }

    if (!newState) {
      state.off({element: elem, type: 'button'}, elemState, recursiveInert)
      state.off({element: elemRefItem, type: 'content'}, elemState, recursiveInert)
    } else {
      state.on({element: elem, type: 'button'}, elemState, recursiveInert)
      state.on({element: elemRefItem, type: 'content'}, elemState, recursiveInert)
    }
  },

  match: (elem, stateRefItem, elemState) => {
    var newState = stateRefItem.classList.contains(elemState)

    if (!newState) {
      state.off({ element: elem, type: 'button' }, elemState)
    } else {
      state.on({ element: elem, type: 'button' }, elemState)
    }
  },

  remove: (options, elemState) => {
    switch (options.type) {
      case 'button':
        options.element.classList.remove(elemState)
        options.element.removeAttribute('aria-expanded')
        break
      case 'content':
        options.element.classList.remove(elemState)
        options.element.removeAttribute('aria-hidden')
        options.element.inert = false
        break
      default:
        break
    }
  }
}

module.exports = state
