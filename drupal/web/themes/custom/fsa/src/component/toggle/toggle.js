import checkMediaQuery from '../../core/helper/checkMediaQuery';

function toggle(toggleButtons, breakpoints) {

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
function measureAccordionContents(  ) {
  document.querySelectorAll('.js-accordion-content')
    .forEach( function( element, index ) {
    var content = element.querySelector(':first-child');
    element.style.setProperty('--expanded' , content.offsetHeight + 'px');
  } );
}

measureAccordionContents();

window.addEventListener('resize', measureAccordionContents);
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
  document.querySelectorAll('.js-toggle-content')
    .forEach( (e) => e.addEventListener("transitionend", measureAccordionContents) );



  function accordionEventHandler( clickEvent )
  {
    clickEvent.preventDefault();
    measureAccordionContents();
    var clickedItem = clickEvent.target;
    var content = clickedItem.nextElementSibling;
    console.log(clickedItem.nextElementSibling);
    content.classList.toggle('is-open');
  }

  document.querySelectorAll('.js-toggle-button')
    .forEach( (e,i) => e.addEventListener('click', accordionEventHandler) );

}

module.exports = toggle;
