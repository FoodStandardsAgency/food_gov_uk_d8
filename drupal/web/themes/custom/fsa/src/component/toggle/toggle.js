import checkMediaQuery from '../../core/helper/checkMediaQuery';
import breakpoints from '../../core/helper/breakpoints';
import cssCustomPropertySupport from '../../core/helper/cssCustomPropertySupport';
import debounce from '../../core/helper/debounce';
import nextByClass from '../../core/helper/nextByClass';

function toggle() {

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
    element.classList.remove('is-visible');
    element.classList.add('is-hidden');
    element.setAttribute('aria-hidden', true);
  }

  function accordionContentOpen(element) {
    element.classList.add('is-visible');
    element.classList.remove('is-hidden');
    element.setAttribute('aria-hidden', false);
  }

  function accordionEventHandler(button, content) {
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

  // All the toggle buttons
  const toggleButtonElementArray = [...document.querySelectorAll('.js-toggle-button')];

  // Check everything found
  if (toggleButtonElementArray <= 0) {
    return;
  }

  // All content elements
  let contentElementArray = [];

  // Loop
  toggleButtonElementArray.forEach((element) => {
    let toggleButton = element;
    let content = nextByClass(toggleButton, 'js-toggle-content');

    if (content === null) {
      content = toggleButton.closest('.js-content-next').nextElementSibling;
      toggleButton = element.closest('.js-content-next');
    }

    contentElementArray = [...contentElementArray, content];
    
    // Add click listener to toggle
    toggleButton.addEventListener('click', function(e){
      e.preventDefault();
      accordionEventHandler(toggleButton, content);
    });

    toggleButton.addEventListener('mouseenter', function(e){
      content.style.willChange = 'max-height, min-height';
    });

    toggleButton.addEventListener('mouseout', function(e){
      content.style.willChange = 'auto';
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
