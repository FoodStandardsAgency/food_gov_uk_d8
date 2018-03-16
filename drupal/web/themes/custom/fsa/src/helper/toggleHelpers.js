const state = {
  on: (options, elemState) => {
    switch (options.type) {
      case 'button':
        options.element.classList.add(elemState)
        options.element.setAttribute('aria-expanded', 'true')
        break
      case 'content':
        options.element.classList.add(elemState)
        options.element.inert = false
        options.element.setAttribute('aria-hidden', 'false')
        break
      default:
        break
    }
  },

  off: (options, elemState) => {
    switch (options.type) {
      case 'button':
        options.element.classList.remove(elemState)
        options.element.setAttribute('aria-expanded', 'false')
        break
      case 'content':
        options.element.classList.remove(elemState)
        options.element.inert = true
        options.element.setAttribute('aria-hidden', 'true')
        break
      default:
        break
    }
  },

  toggle: (elem, elemRefItem, elemState) => {
    if (elemRefItem.classList.contains(elemState)) {
      state.off({element: elem, type: 'button'}, elemState)
      state.off({element: elemRefItem, type: 'content'}, elemState)
    } else {
      state.on({element: elem, type: 'button'}, elemState)
      state.on({element: elemRefItem, type: 'content'}, elemState)
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
