import setHeight from '../../core/helper/setHeight';
import checkMediaQuery from '../../core/helper/checkMediaQuery';
import breakpoints from '../../core/helper/breakpoints';
import debounce from '../../core/helper/debounce';
import nextByClass from '../../core/helper/nextByClass';
import inert from 'wicg-inert';

function toggle() {

  // SWIPE DETECT HELPER
  //----------------------------------------------

  var swipeDetect = function(el, callback){ 
    var touchsurface = el,
    swipedir,
    startX,
    startY,
    dist,
    distX,
    distY,
    threshold = 100, //required min distance traveled to be considered swipe
    restraint = 100, // maximum distance allowed at the same time in perpendicular direction
    allowedTime = 300, // maximum time allowed to travel that distance
    elapsedTime,
    startTime,
    eventObj,
    handleswipe = callback || function(swipedir, eventObj){}

    touchsurface.addEventListener('touchstart', function(e){
      var touchobj = e.changedTouches[0]
      swipedir = 'none'
      dist = 0
      startX = touchobj.pageX
      startY = touchobj.pageY
      startTime = new Date().getTime() // record time when finger first makes contact with surface
      eventObj = e;
    }, false)

    touchsurface.addEventListener('touchend', function(e){
      var touchobj = e.changedTouches[0]
      distX = touchobj.pageX - startX // get horizontal dist traveled by finger while in contact with surface
      distY = touchobj.pageY - startY // get vertical dist traveled by finger while in contact with surface
      elapsedTime = new Date().getTime() - startTime // get time elapsed
      if (elapsedTime <= allowedTime){ // first condition for awipe met
        if (Math.abs(distX) >= threshold && Math.abs(distY) <= restraint){ // 2nd condition for horizontal swipe met
          swipedir = (distX < 0)? 'left' : 'right' // if dist traveled is negative, it indicates left swipe
        }
        else if (Math.abs(distY) >= threshold && Math.abs(distX) <= restraint){ // 2nd condition for vertical swipe met
          swipedir = (distY < 0)? 'up' : 'down' // if dist traveled is negative, it indicates up swipe
        }
      }
      handleswipe(swipedir, eventObj)
    }, false)
  }


  // CLOSEST PARENT HELPER FUNCTION
  //----------------------------------------------

  var closestParent = function(child, match) {
    if (!child || child == document) {
      return null;
    }
    if (child.classList.contains(match) || child.nodeName.toLowerCase() == match) {
      return child;
    }
    else {
      return closestParent(child.parentNode, match);
    }
  }


  // REUSABLE FUNCTION
  //----------------------------------------------

  function getElemRef(elem, dataState) {
    // Get reference element or array
    if(elem.getAttribute("data-state-element")) {
      const dataStateElementValue = elem.getAttribute("data-state-element");
      return [...document.querySelectorAll(dataStateElementValue)];
    } else {
      return elem.nextSibling;
    }
  }

  function getElemScope(elem, parentSelector, targetButtonSelector, targetContentSelector) {
    // Grab parent
    var elemParent = closestParent(elem, parentSelector);
    // Grab all matching child elements of parent
    return {
      button: [...elemParent.querySelectorAll(targetButtonSelector)],
      content: [...elemParent.querySelectorAll(targetContentSelector)]
    };
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

  function toggleState(elem, elemRefItem, elemState) {
    console.log(elemRefItem.classList.contains(elemState));
    if (elemRefItem.classList.contains(elemState)) {
      setStateOff({element: elem, type: 'button'}, elemState);
      setStateOff({element: elemRefItem, type: 'content'}, elemState);
    } else {
      setStateOn({element: elem, type: 'button'}, elemState);
      setStateOn({element: elemRefItem, type: 'content'}, elemState);
    }
  }

  function getElemState(elem) {
    // Grab data-state list and convert to array
    var dataState = elem.getAttribute("data-state");
    return dataState.split(", ");
  }

  function setDefaultState(elem, elemRef, elemState) {
    // Set default state for the 'button'
    setStateOff({element: elem, type: 'button'}, elemState);

    elemRef.forEach(elemRefItem => {
      // Grab data-state-behaviour list if present and convert to array
      if(elem.getAttribute("data-breakpoint")) {
        var dataBreakpoint = elem.getAttribute("data-breakpoint");
        dataBreakpoint = dataBreakpoint.split(", ");

        dataBreakpoint.forEach(breakpoint => {
          elemRefItem.classList.add(`is-${breakpoint}`);

          switch (breakpoint) {
            case "mobile":
              if (checkMediaQuery() != breakpoints.small) {
                setStateOn({element: elemRefItem, type: 'content'}, elemState);
              } else {
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
              break;
            case "desktop":
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
  
  // Init function
  function initDataState(elem){
    // Get elem state
    var elemState = getElemState(elem);

    // Get ref elements
    var elemRef = getElemRef(elem, elemState);

    // Set reference element theme
    setDefaultState(elem, elemRef, elemState);

    // Add listeners
    // Detect data-swipe attribute before we do anything, as its optional
    // If not present, assign click event like before
    if(elem.getAttribute("data-state-swipe")){
      // Grab swipe specific data from data-state-swipe
      var elemSwipe = elem.getAttribute("data-state-swipe"),
          elemSwipe = elemSwipe.split(", "),
          direction = elemSwipe[0],
          elemSwipeBool = elemSwipe[1],
          currentElem = elem;

      // If the behaviour flag is set to "false", or not set at all, then assign our click event
      if(elemSwipeBool === "false" || !elemSwipeBool) {
        // Assign click event
        elem.addEventListener("click", function(e){
          // Prevent default action of element
          e.preventDefault(); 
          // Run state function
          processChange(this, elemRef, elemState);
        });
      }
      // Use our swipeDetect helper function to determine if the swipe direction matches our desired direction
      swipeDetect(elem, function(swipedir){
        if(swipedir === direction) {
          // Run state function
          processChange(currentElem, elemRef, elemState);
        }
      })
    }
    else {
      // Assign click event
      elem.addEventListener("click", function(e){
        // Prevent default action of element
        e.preventDefault(); 
        // Run state function
        processChange(this, elemRef, elemState);
      });
    }
    // Add keyboard event for enter key to mimic anchor functionality
    elem.addEventListener("keypress", function(e){
      if(e.which === 13) {
        // Prevent default action of element
        e.preventDefault();
        // Run state function
        processChange(this, elemRef, elemState);
      }
    });
  };

  function initialize() {
    // Grab all elements with required attributes
    var elems = document.querySelectorAll("[data-state]");
  
    // Loop through our matches and add click events
    for(var a = 0; a < elems.length; a++){
      initDataState(elems[a]);
    }
  }

  // Setup mutation observer to track changes for matching elements added after initial DOM render
  var observer = new MutationObserver(function(mutations) {
    mutations.forEach(function(mutation) {
      for(var d = 0; d < mutation.addedNodes.length; d++) {
        // Check if we're dealing with an element node
        if(typeof mutation.addedNodes[d].getAttribute === 'function') {
          if(mutation.addedNodes[d].getAttribute("data-state")) {
            initDataState(mutation.addedNodes[d]);
          }
        }
      }
    });    
  });

  // Define type of change our observer will watch out for
  observer.observe(document.body, {
    childList: true,
    subtree: true
  });

  // const resizeHandler = debounce(function() {
  //   initialize();
  // }, 250);

  // window.addEventListener('resize', resizeHandler);

  initialize();
}

module.exports = toggle;
