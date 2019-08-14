/* global MutationObserver */
import tabbable from 'tabbable'
import setHeight from '../../helper/setHeight'
import checkMediaQuery from '../../helper/checkMediaQuery'
import breakpoints from '../../helper/breakpoints'
import debounce from '../../helper/debounce'
import closestParent from '../../helper/closestParent'
import state from '../../helper/toggleHelpers'

function toggle () {
  const KEYCODE = {
    ENTER: 13,
    ESC: 27,
    SPACE: 32
  }

  // Get content element the button is referencing to
  function getElemRef (elem) {
    // Get reference element or array
    if (elem.getAttribute('data-state-element')) {
      const dataStateElementValue = elem.getAttribute('data-state-element')
      return [...document.querySelectorAll(dataStateElementValue)]
    }
    return elem.nextSibling
  }

  // Get content element scope
  function getElemScope (elem, parentSelector, targetButtonSelector, targetContentSelector) {
    // Grab parent
    const elemParent = closestParent(elem, parentSelector)
    // Grab all matching child elements of parent
    return {
      button: [...elemParent.querySelectorAll(targetButtonSelector)],
      content: [...elemParent.querySelectorAll(targetContentSelector)]
    }
  }

  // Get elemenet state
  function getElemState (elem) {
    // Grab data-state list and convert to array
    const dataState = elem.getAttribute('data-state')
    return dataState.split(', ')
  }

  // Set default state
  function setDefaultState (elem, elemRef, elemState) {
    // Set default state for the 'button'
    state.off({ element: elem, type: 'button' }, elemState)

    elemRef.forEach((elemRefItem) => {
      if (elem.getAttribute('data-breakpoint')) {
        let dataBreakpoint = elem.getAttribute('data-breakpoint')
        dataBreakpoint = dataBreakpoint.split(', ')

        dataBreakpoint.forEach((breakpoint) => {
          elemRefItem.classList.add(`is-${breakpoint}`)

          switch (breakpoint) {
            case 'mobile':

              if (checkMediaQuery() === breakpoints.small ||
              checkMediaQuery() === breakpoints.xsmall) {
                // Set state off
                state.off({
                  element: elemRefItem,
                  type: 'content'
                }, elemState)

                // Set theme
                if (elem.getAttribute('data-theme')) {
                  let dataStateTheme = elem.getAttribute('data-theme')
                  dataStateTheme = dataStateTheme.split(', ')
                  dataStateTheme.forEach((theme) => {
                    elemRefItem.classList.add(`is-${theme}`)

                    switch (theme) {
                      case 'dynamic':
                        setHeight(elemRefItem)
                        break
                      case 'popup':
                        break

                      default:
                        break
                    }
                  })
                }
              } else {
                // Set state on
                state.on({ element: elemRefItem, type: 'content' }, elemState)

                // Remove theme
                if (elem.getAttribute('data-theme')) {
                  let dataStateTheme = elem.getAttribute('data-theme')
                  dataStateTheme = dataStateTheme.split(', ')
                  dataStateTheme.forEach((theme) => {
                    elemRefItem.classList.remove(`is-${theme}`)
                  })
                }
              }
              break
            case 'desktop':
              break
            case 'touch':
              break
            default:
              break
          }
        })
      } else {
        // Set default state for the 'content'
        state.off({ element: elemRefItem, type: 'content' }, elemState)

        // Set theme
        if (elem.getAttribute('data-theme')) {
          let dataStateTheme = elem.getAttribute('data-theme')
          dataStateTheme = dataStateTheme.split(', ')

          dataStateTheme.forEach((theme) => {
            elemRefItem.classList.add(`is-${theme}`)

            switch (theme) {
              case 'dynamic':
                setHeight(elemRefItem)
                break
              case 'popup':
                break

              default:
                break
            }
          })
        }
      }
    })
  }

  // Change function
  function processChange (elem, elemRef, elemState) {
    let dataStateScope
    let dataStateScopeButton
    let dataStateScopeContent
    let elemScopeObject
    let elemBehaviour

    // Grab data-scope list if present and convert to array
    if (elem.getAttribute('data-state-scope') &&
    elem.getAttribute('data-state-scope-button') &&
    elem.getAttribute('data-state-scope-content')) {
      dataStateScope = elem.getAttribute('data-state-scope')
      dataStateScopeButton = elem.getAttribute('data-state-scope-button')
      dataStateScopeContent = elem.getAttribute('data-state-scope-content')
      elemScopeObject =
      getElemScope(elem, dataStateScope, dataStateScopeButton, dataStateScopeContent)
    }

    // Grab data-state-behaviour list if present and convert to array
    if (elem.getAttribute('data-state-behaviour')) {
      elemBehaviour = elem.getAttribute('data-state-behaviour')
    }

    // Do
    elemRef.forEach((elemRefItem) => {
      switch (elemBehaviour) {
        case 'add':
          state.on({ element: elem, type: 'button' }, elemState)
          state.on({ element: elemRefItem, type: 'content' }, elemState)
          break

        case 'remove':
          state.off({ element: elem, type: 'button' }, elemState)
          state.off({ element: elemRefItem, type: 'content' }, elemState)
          break

        case 'remove-all':
          elemScopeObject.button.forEach((elemScopeButtonArrayItem) => {
            if (elem !== elemScopeButtonArrayItem) {
              state.off({ element: elemScopeButtonArrayItem, type: 'button' }, elemState)
            }
          })

          elemScopeObject.content.forEach((elemScopeContentArrayItem) => {
            if (elemRefItem !== elemScopeContentArrayItem) {
              state.off({ element: elemScopeContentArrayItem, type: 'content' }, elemState)
            }
          })
          state.toggle(elem, elemRefItem, elemState)
          break

        default:
          state.toggle(elem, elemRefItem, elemState)
          break
      }
    })
  }

  // Prepare elements
  function prepareElements (elem, elemRef, elemState) {
    // Add tabindex if not tabbable
    if (tabbable(elem).length === 0) {
      elem.setAttribute('tabindex', '0')
    }

    // Add listeners
    // Assign click event
    elem.addEventListener('click', function clickEvent (e) {
      // TODO Prevet this happening when pressing SPACE on BUTTON element
      // Prevent default action of element
      e.preventDefault()
      // Run state function
      processChange(this, elemRef, elemState)
    })

    // Add keyboard event for enter key to mimic anchor functionality
    elem.addEventListener('keypress', function keypressEvent (e) {
      if (e.which === KEYCODE.SPACE || e.which === KEYCODE.ENTER) {
        // Prevent default action of element
        e.preventDefault()
        // Run state function
        processChange(this, elemRef, elemState)
      }
    })
  }

  function initialize (elems) {
    // Loop through our matches
    for (let a = 0; a < elems.length; a++) {
      // Get elem state
      const elemState = getElemState(elems[a])

      // Get ref elements
      const elemRef = getElemRef(elems[a])

      // Prepare elements
      prepareElements(elems[a], elemRef, elemState)

      // Set default state
      setDefaultState(elems[a], elemRef, elemState)
    }
  }

  // Setup mutation observer to track changes for matching elements added after initial DOM render
  const observer = new MutationObserver((mutations) => {
    mutations.forEach((mutation) => {
      for (let d = 0; d < mutation.addedNodes.length; d++) {
        // Check if we're dealing with an element node
        if (typeof mutation.addedNodes[d].getAttribute === 'function') {
          if (mutation.addedNodes[d].getAttribute('data-state')) {
            // Get elem state
            const elemState = getElemState(mutation.addedNodes[d])

            // Get ref elements
            const elemRef = getElemRef(mutation.addedNodes[d])

            // Prepare elements
            prepareElements(mutation.addedNodes[d], elemRef, elemState)

            // Set default state
            setDefaultState(mutation.addedNodes[d], elemRef, elemState)
          }
        }
      }
    })
  })

  // Grab all elements with required attributes
  const elems = [...document.querySelectorAll('[data-state]')];

  // Current window width
  let windowWidth = window.innerWidth

  // Define type of change our observer will watch out for
  observer.observe(document.body, {
    childList: true,
    subtree: true
  })

  const resizeHandler = debounce(() => {
    // Check if vertical resizing
    if (window.innerWidth === windowWidth) {
      return false
    }

    windowWidth = window.innerWidth

    // Loop through our matches
    for (let a = 0; a < elems.length; a++) {
      // Get elem state
      const elemState = getElemState(elems[a])

      // Get ref elements
      const elemRef = getElemRef(elems[a])

      // Set default state
      setDefaultState(elems[a], elemRef, elemState)
    }
  }, 250)

  window.addEventListener('resize', resizeHandler)

  initialize(elems)
}

module.exports = toggle
