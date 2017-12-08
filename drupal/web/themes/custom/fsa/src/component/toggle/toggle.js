import checkMediaQuery from '../../core/helper/checkMediaQuery';
import cssCustomPropertySupport from '../../core/helper/cssCustomPropertySupport';

function toggle() {

  // const toggleContentVisibility = (button, content) => {
  //   if (content.classList.contains('is-open')) {
  //     content.classList.remove('is-open');
  //     content.setAttribute('aria-hidden', true);
  //     button.classList.remove('is-open');
  //     button.setAttribute('aria-expanded', false);
  //   } else {
  //     content.classList.add('is-open');
  //     content.setAttribute('aria-hidden', false);
  //     button.classList.add('is-open');
  //     button.setAttribute('aria-expanded', true);
  //   }
  // }

  // if (toggleButtons.length != undefined) {
  //   [...toggleButtons].forEach(function(button) {
  //     let content;
  //     let toggleButton;

  //     if (button.nextElementSibling === null) {
  //       content = button.closest('.js-content-next').nextElementSibling;
  //       toggleButton = button.closest('.js-content-next');
  //     } else {
  //       content = button.nextElementSibling;
  //       toggleButton = button;
  //     }

  //     if (content.classList.contains('js-toggle-content-only-mobile')) {
  //       if (checkMediaQuery() == breakpoints.medium) {
  //         content.removeAttribute('aria-hidden');
  //       }
  //     }
  //     // Add click event
  //     button.addEventListener("click", function() {
  //       toggleContentVisibility(toggleButton, content);
  //     });
  //   });
  // } else {
  //   let content = toggleButtons.nextElementSibling;

  //   if (content.classList.contains('js-toggle-content-only-mobile')) {
  //     if (checkMediaQuery() == breakpoints.medium) {
  //       content.removeAttribute('aria-hidden');
  //     }
  //   }
  //   toggleButtons.setAttribute('role', 'button');

  //   // Add click event
  //   toggleButtons.addEventListener("click", function() {
  //     toggleContentVisibility(toggleButtons, content);
  //   });
  // }

  // Measure all content elements and assign their height to a css variable in the style attribute of the html.
  function measureAccordionContents(element) {
    let childrenCombinedHeight = 0;
    [...element.children].forEach((child) => {
      childrenCombinedHeight = childrenCombinedHeight + child.offsetHeight;
    });
    element.style.setProperty('--expanded' , `${childrenCombinedHeight}px`);
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

  function accordionEventHandler(button, content) {
    if (content.classList.contains('is-open')) {
      content.classList.remove('is-open');
      content.classList.add('is-closed');
      content.setAttribute('aria-hidden', true);
      button.classList.remove('is-open');
      button.setAttribute('aria-expanded', false);
    } else {
      content.classList.add('is-open');
      content.classList.remove('is-closed');
      content.setAttribute('aria-hidden', false);
      button.classList.add('is-open');
      button.setAttribute('aria-expanded', true);
    }
  }

  // All the toggle buttons
  const toggleButtonElementArray = [...document.querySelectorAll('.js-toggle-button')];

  // Check everything found
  if (toggleButtonElementArray <= 0) {
    return;
  }

  // Loop
  toggleButtonElementArray.forEach((element) => {
    const toggleButtonElement = element;
    let toggleButton;
    let content;

    if (toggleButtonElement.nextElementSibling === null) {
      content = toggleButtonElement.closest('.js-content-next').nextElementSibling;
      toggleButton = toggleButtonElement.closest('.js-content-next');
    } else {
      content = toggleButtonElement.nextElementSibling;
      toggleButton = toggleButtonElement;
    }

    // Initial measurmenet for content element
    measureAccordionContents(content);
    
    // Add click listener to toggle
    toggleButtonElement.addEventListener("click", function(e){
      e.preventDefault();
      accordionEventHandler(toggleButton, content);
    });

    // Add transitioned listener to content
    content.addEventListener("transitionend", function(e){
      measureAccordionContents(content);
    });
  });
    
}

module.exports = toggle;
