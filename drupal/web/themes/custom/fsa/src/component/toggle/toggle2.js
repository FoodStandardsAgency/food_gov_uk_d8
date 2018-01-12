import checkMediaQuery from '../../core/helper/checkMediaQuery';
import breakpoints from '../../core/helper/breakpoints';
import cssCustomPropertySupport from '../../core/helper/cssCustomPropertySupport';
import debounce from '../../core/helper/debounce';
import nextByClass from '../../core/helper/nextByClass';
import inert from 'wicg-inert';
import tabbable from 'tabbable';

function toggle() {

  const KEYCODE = {
    ESC: 27,
    SPACE: 32,
  }

  // Measure all content elements and assign their height to a css variable in the style attribute of the html.
  function setDynamicHeight(element) {
    element.classList.remove('is-automated-height');
    element.classList.add('is-dynamic-height');

    let childrenCombinedHeight = 0;
    [...element.children].forEach((child) => {
      childrenCombinedHeight = childrenCombinedHeight + child.offsetHeight;
    });
    element.style.setProperty('--expanded' , `${childrenCombinedHeight}px`);
  }

  function setAutomaticHeight(element) {
    element.classList.add('is-automated-height');
    element.classList.remove('is-dynamic-height');
  }
  /*
  @todo - Needs to be rerun whenever 
          dom changes affect the width
          of the parent or the height of 
          the children.
  @todo - At narrow viewport widths if
          there is no scrollbar intially 
          the content height is measuerd 
          for that viewport.
          Then when the content is shown 
          and it is long enough to trigger 
          a scrollbar, the initially measured 
          height is no longer adequate.
  */

  function accordionButtonClose(element) {
    element.classList.remove('is-open');
    element.classList.add('is-closed');
    element.setAttribute('aria-expanded', false);
  }

  function accordionButtonOpen(element) {
    element.classList.remove('is-closed');
    element.classList.add('is-open');
    element.setAttribute('aria-expanded', true);
  }

  function accordionContentClose(element) {
    if (element.classList.contains('js-toggle-content-function-open')  && checkMediaQuery() != breakpoints.small) {
      return false;
    } else {
      element.classList.remove('is-visible');
      element.classList.add('is-hidden');
      element.setAttribute('aria-hidden', true);

      // Unset content from accessibility tree
      element.inert = true;
    }
  }

  function accordionContentOpen(element) {
    element.classList.add('is-visible');
    element.classList.remove('is-hidden');
    element.setAttribute('aria-hidden', false);

    // Set content to accessibility tree
    element.inert = false;
  }

  function accordionEventHandler(button, content, event) {
    // console.log(event.type  + ' ' + content.classList.contains('is-visible'));
    // if (document.activeElement.classList.contains('js-toggle-button')) {
    //   console.log(button === document.activeElement)
    // } else {
    //   console.log(button);
    //   console.log(document.activeElement.closest('.js-toggle-button'));
    //   console.log(button === document.activeElement.closest('.js-toggle-button'));
    // }
    
    if (content.classList.contains('is-visible')) {
      accordionContentClose(content);
      accordionButtonClose(button);
    } else {
      accordionContentOpen(content);
      accordionButtonOpen(button);
    }
  }

  function resetAccordion(buttonArray, contentArray) {
    buttonArray.forEach((element) => {
      // Check if only mobile
      if (element.classList.contains('js-toggle-content-only-mobile')) {
        if (checkMediaQuery() == breakpoints.small) {
          accordionButtonClose(element);
        } else {
          accordionButtonOpen(element);
        }
      } else {
        accordionButtonClose(element);
      }
    });

    contentArray.forEach((element) => {
      if (element.classList.contains('js-toggle-content-function-open') && checkMediaQuery() != breakpoints.small) {

      } else {
        // Unset content from accessibility tree
        element.inert = true;
      }

      // Check if only mobile
      if (element.classList.contains('js-toggle-content-only-mobile')) {
        if (checkMediaQuery() == breakpoints.small) {
          setDynamicHeight(element);
          accordionContentClose(element);
        } else {
          setAutomaticHeight(element);
          accordionContentOpen(element);
        }
      } else {
        setDynamicHeight(element);
        accordionContentClose(element);
      }
    });
  };

  // Query all the toggle buttons
  const toggleButtonElementArray = [...document.querySelectorAll('.js-toggle-button')];

  // Query all the toggle groups
  const toggleGroupElementArray = [...document.querySelectorAll('.js-toggle-group')];

  // Check everything found
  if (toggleButtonElementArray <= 0) {
    return;
  }

  // All content elements
  let contentElementArray = [];
  let toggleGroupItemArray = [];

  // Loop
  toggleButtonElementArray.forEach((element) => {
    let toggleButton = element;
    let toggleFunction = undefined;
    let content = nextByClass(toggleButton, 'js-toggle-content');
    let contentTheme = undefined;
    let groupID = undefined;

    // Set button focusable if not tabbable or has tabbable children
    if (tabbable(toggleButton).length === 0) {
      toggleButton.setAttribute('tabindex', '0');
    }

    // Check if button specific themes and functions
    const toggleButtonClassListArray = toggleButton.classList.value.split(' ');

    toggleButtonClassListArray.forEach((className) => {
      if (className.indexOf('js-toggle-button-function') !== -1) {
        toggleFunction = className.split('js-toggle-button-function-').pop();
      }
    });

    // Check if content has a next sibling
    if (content === null) {
      if (toggleFunction === 'current') {
        content = toggleButton.closest('.js-toggle-content');
      } else {
        content = toggleButton.closest('.js-content-next').nextElementSibling;
      }
    }

    contentElementArray = [...contentElementArray, content];    

    // Check if content specific themes and functions
    const contentClassListArray = content.classList.value.split(' ');

    contentClassListArray.forEach((className) => {
      if (className.indexOf('js-toggle-theme') !== -1) {
        contentTheme = className.split('js-toggle-theme-').pop();
        content.classList.add(`is-${contentTheme}`);
      }

      if (className.indexOf('js-toggle-content-function') !== -1) {
        toggleFunction = className.split('js-toggle-content-function-').pop();
      }
    });

    // // Groups
    // if (toggleGroupElementArray.indexOf(toggleButton.closest('.js-toggle-group')) >= 0) {
    //   groupID = toggleGroupElementArray.indexOf(toggleButton.closest('.js-toggle-group'));
    //   // toggleGroupItemArray[groupID].push(toggleButton);
    //   // toggleGroupItemArray[groupID] = [...toggleGroupItemArray[groupID], toggleButton];

    //   toggleGroupItemArray[groupID] = [...toggleGroupItemArray[groupID], toggleButton];
    //   console.log(toggleGroupItemArray[groupID]);
    // }

    // Add click listener to toggle
    toggleButton.addEventListener('mousedown', function(event){
      event.preventDefault();  //stops default browser action (focus)
      accordionEventHandler(toggleButton, content, event);
    });

    // Add click listener to toggle
    toggleButton.addEventListener('click', function(event){
      event.preventDefault();
    });

    // toggleButton.addEventListener('blur', function(event) {
  //   content.style.willChange = 'auto';
    //   accordionEventHandler(toggleButton, content, event);
    // }, true);

    toggleButton.addEventListener('mouseenter', function(event){
      content.style.willChange = 'max-height, min-height';
    });

    toggleButton.addEventListener('mouseout', function(event){
      content.style.willChange = 'auto';
    });

    // Add click listener to toggle
    toggleButton.addEventListener('keydown', function(event){
      if (event.keyCode === KEYCODE.SPACE) {
        event.preventDefault();
        accordionEventHandler(toggleButton, content, event);
      }
    });
    
    // // Add transitioned listener to content
    // content.addEventListener("transitionend", function(e){
    //   setDynamicHeight(content);
    // });
  });
  
  resetAccordion(toggleButtonElementArray, contentElementArray);

  const resizeHandler = debounce(function() {
    resetAccordion(toggleButtonElementArray, contentElementArray);
  }, 250);

  window.addEventListener('resize', resizeHandler);
}

module.exports = toggle;
