// Set state off
function setStateOff (options, elemState) {
  const element = options.element
  switch (options.type) {
    case 'button':
      element.classList.remove(elemState)
      element.setAttribute('aria-expanded', 'false')
      break
    case 'content':
      element.classList.remove(elemState)
      element.setAttribute('aria-hidden', 'false')
      element.inert = true
      // console.log('content off, aria-hidden=', element.getAttribute('aria-hidden'));
      break
    default:
      break
  }
}

// Set state on
function setStateOn (options, elemState) {
  const element = options.element

  switch (options.type) {
    case 'button':
      element.classList.add(elemState)
      element.setAttribute('aria-expanded', 'true')
      break
    case 'content':
      element.classList.add(elemState)
      element.setAttribute('aria-hidden', 'false')
      element.inert = false
      // console.log('content on, aria-hidden=', element.getAttribute('aria-hidden'));
      break
    default:
      break
  }
}

// Remove state
function removeState (options, elemState) {
  const element = options.element

  switch (options.type) {
    case 'button':
      element.classList.remove(elemState)
      element.removeAttribute('aria-expanded')
      break
    case 'content':
      element.classList.remove(elemState)
      element.removeAttribute('aria-hidden')
      element.inert = false
      break
    default:
      break
  }
}

// Toggle state
function toggleState (elem, elemRefItem, elemState) {
  if (elemRefItem.classList.contains(elemState)) {
    setStateOff({element: elem, type: 'button'}, elemState)
    setStateOff({element: elemRefItem, type: 'content'}, elemState)
  } else {
    setStateOn({element: elem, type: 'button'}, elemState)
    setStateOn({element: elemRefItem, type: 'content'}, elemState)
  }
}

module.exports = { setStateOff, setStateOn, removeState, toggleState }
