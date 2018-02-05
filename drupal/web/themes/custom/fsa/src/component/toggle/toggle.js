import setHeight from '../../core/helper/setHeight';
import checkMediaQuery from '../../core/helper/checkMediaQuery';
import breakpoints from '../../core/helper/breakpoints';
import debounce from '../../core/helper/debounce';
import nextByClass from '../../core/helper/nextByClass';
import closestParent from '../../core/helper/closestParent';
import { setStateOff, setStateOn, toggleState } from '../../core/helper/toggleHelpers';
import inert from 'wicg-inert';
import tabbable from 'tabbable';

function toggle() {

  const KEYCODE = {
    ENTER: 13,
    ESC: 27,
    SPACE: 32,
  }

  // Get content element the button is referencing to
  function getElemRef(elem, dataState) {
    // Get reference element or array
    if(elem.getAttribute("data-state-element")) {
      const dataStateElementValue = elem.getAttribute("data-state-element");
      return [...document.querySelectorAll(dataStateElementValue)];
    } else {
      return elem.nextSibling;
    }
  }

  // Get content element scope
  function getElemScope(elem, parentSelector, targetButtonSelector, targetContentSelector) {
    // Grab parent
    var elemParent = closestParent(elem, parentSelector);
    // Grab all matching child elements of parent
    return {
      button: [...elemParent.querySelectorAll(targetButtonSelector)],
      content: [...elemParent.querySelectorAll(targetContentSelector)]
    };
  }

  // Get elemenet state
  function getElemState(elem) {
    // Grab data-state list and convert to array
    var dataState = elem.getAttribute("data-state");
    return dataState.split(", ");
  }

  // Set default state
  function setDefaultState(elem, elemRef, elemState) {
    // Set default state for the 'button'
    setStateOff({element: elem, type: 'button'}, elemState);

    elemRef.forEach(elemRefItem => {
      if(elem.getAttribute("data-breakpoint")) {
        var dataBreakpoint = elem.getAttribute("data-breakpoint");
        dataBreakpoint = dataBreakpoint.split(", ");

        dataBreakpoint.forEach(breakpoint => {
          elemRefItem.classList.add(`is-${breakpoint}`);

          switch (breakpoint) {
            case "mobile":
            
              if (checkMediaQuery() === breakpoints.small || checkMediaQuery() === breakpoints.xsmall) {
                // Set state off
                setStateOff({element: elemRefItem, type: 'content'}, elemState);

                // Set theme
                if(elem.getAttribute("data-theme")) {
                  var dataStateTheme = elem.getAttribute("data-theme");
                  dataStateTheme = dataStateTheme.split(", ");
                  dataStateTheme.forEach(theme => {
                    elemRefItem.classList.add(`is-${theme}`);
          
                    switch (theme) {
                      case "dynamic":
                        setHeight(elemRefItem);
                        break;
                      case "popup":
                        break;
          
                      default:
                        break;
                    }
                  });
                }
              } else {
                // Set state on
                setStateOn({element: elemRefItem, type: 'content'}, elemState);

                // Remove theme
                if(elem.getAttribute("data-theme")) {
                  var dataStateTheme = elem.getAttribute("data-theme");
                  dataStateTheme = dataStateTheme.split(", ");
                  dataStateTheme.forEach(theme => {
                    elemRefItem.classList.remove(`is-${theme}`);
                  });
                }
              }
              break;
            case "desktop":
              break;
            case "touch":
              break;
            default:
              break;
          }
        });
      } else {
        // Set default state for the 'content'
        setStateOff({element: elemRefItem, type: 'content'}, elemState);
        
        // Set theme
        if(elem.getAttribute("data-theme")) {
          var dataStateTheme = elem.getAttribute("data-theme");
          dataStateTheme = dataStateTheme.split(", ");

          dataStateTheme.forEach(theme => {
            elemRefItem.classList.add(`is-${theme}`);

            switch (theme) {
              case "dynamic":
                setHeight(elemRefItem);
                break;
              case "popup":
                break;

              default:
                break;
            }
          });
        }
      }
    });
  }

  // Change function
  function processChange(elem, elemRef, elemState) {
    let dataStateScope;
    let dataStateScopeButton;
    let dataStateScopeContent;
    let elemScopeObject;
    let elemBehaviour;

    // Grab data-scope list if present and convert to array
    if(elem.getAttribute("data-state-scope") && elem.getAttribute("data-state-scope-button") && elem.getAttribute("data-state-scope-content")) {
      dataStateScope = elem.getAttribute("data-state-scope");
      dataStateScopeButton = elem.getAttribute("data-state-scope-button");
      dataStateScopeContent = elem.getAttribute("data-state-scope-content");
      elemScopeObject = getElemScope(elem, dataStateScope, dataStateScopeButton, dataStateScopeContent);
    }

    // Grab data-state-behaviour list if present and convert to array
    if(elem.getAttribute("data-state-behaviour")) {
      elemBehaviour = elem.getAttribute("data-state-behaviour");
    }

    // Do
    elemRef.forEach(elemRefItem => {
      switch (elemBehaviour) {
        case 'add':
          setStateOn({element: elem, type: 'button'}, elemState);
          setStateOn({element: elemRefItem, type: 'content'}, elemState);
          break;

        case 'remove':
          setStateOff({element: elem, type: 'button'}, elemState);
          setStateOff({element: elemRefItem, type: 'content'}, elemState);
          break;
        
        case 'remove-all':
          elemScopeObject.button.forEach(elemScopeButtonArrayItem => {
            if (elem !== elemScopeButtonArrayItem) {
              setStateOff({element: elemScopeButtonArrayItem, type: 'button'}, elemState);              
            }
          });

          elemScopeObject.content.forEach(elemScopeContentArrayItem => {
            if (elemRefItem !== elemScopeContentArrayItem) {
              setStateOff({element: elemScopeContentArrayItem, type: 'content'}, elemState);
            }
          });
          toggleState(elem, elemRefItem, elemState);
          break;

        default:
          toggleState(elem, elemRefItem, elemState);
          break;
      }
    });
  };
  
  // Prepare elements
  function prepareElements(elem, elemRef, elemState){

    // Add tabindex if not tabbable
    if (tabbable(elem).length === 0) {
      elem.setAttribute('tabindex', '0');
    }

    // Add listeners
    // Assign click event
    elem.addEventListener("click", function(e){
      // TODO Prevet this happening when pressing SPACE on BUTTON element
      // Prevent default action of element
      e.preventDefault(); 
      // Run state function
      processChange(this, elemRef, elemState);
    });

    // Add keyboard event for enter key to mimic anchor functionality
    elem.addEventListener("keypress", function(e) {
      if(e.which === KEYCODE.SPACE || e.which === KEYCODE.ENTER) {
        // Prevent default action of element
        e.preventDefault();
        // Run state function
        processChange(this, elemRef, elemState);
      }
    });
  };

  function initialize(elems) {
    // Loop through our matches
    for(var a = 0; a < elems.length; a++){
      // Get elem state
      var elemState = getElemState(elems[a]);

      // Get ref elements
      var elemRef = getElemRef(elems[a], elemState);

      // Prepare elements
      prepareElements(elems[a], elemRef, elemState);

      // Set default state
      setDefaultState(elems[a], elemRef, elemState);
    }
  }

  // Setup mutation observer to track changes for matching elements added after initial DOM render
  var observer = new MutationObserver(function(mutations) {
    mutations.forEach(function(mutation) {
      for(var d = 0; d < mutation.addedNodes.length; d++) {
        // Check if we're dealing with an element node
        if(typeof mutation.addedNodes[d].getAttribute === 'function') {
          if(mutation.addedNodes[d].getAttribute("data-state")) {
            // Get elem state
            var elemState = getElemState(mutation.addedNodes[d]);

            // Get ref elements
            var elemRef = getElemRef(mutation.addedNodes[d], elemState);

            // Prepare elements
            prepareElements(mutation.addedNodes[d], elemRef, elemState);

            // Set default state
            setDefaultState(mutation.addedNodes[d], elemRef, elemState);
          }
        }
      }
    });    
  });

  // Grab all elements with required attributes
  var elems = document.querySelectorAll("[data-state]");

  // Current window width
  let windowWidth = window.innerWidth;

  // Define type of change our observer will watch out for
  observer.observe(document.body, {
    childList: true,
    subtree: true
  });

  const resizeHandler = debounce(function() {

    // Check if vertical resizing
    if (window.innerWidth == windowWidth) {
      return false; 
    }

    windowWidth = window.innerWidth;

    // Loop through our matches
    for(var a = 0; a < elems.length; a++){

      // Get elem state
      var elemState = getElemState(elems[a]);

      // Get ref elements
      var elemRef = getElemRef(elems[a], elemState);

      // Set default state
      setDefaultState(elems[a], elemRef, elemState);
    }
  }, 250);

  window.addEventListener('resize', resizeHandler);

  initialize(elems);
}

module.exports = toggle;
