import checkMediaQuery from './checkMediaQuery';

function toggleContent(toggleButtons, breakpoints, toggleContentElement = null) {

  const toggleContentVisibility = (button, content) => {
    if (content.classList.contains('is-open')) {
      content.classList.remove('is-open');
      content.setAttribute('aria-hidden', true);
      button.classList.remove('is-open');
      button.setAttribute('aria-expanded', false);
    } else {
      content.classList.add('is-open');
      content.setAttribute('aria-hidden', false);
      button.classList.add('is-open');
      button.setAttribute('aria-expanded', true);
    }
  }

  if (toggleButtons.length != undefined) {
    [...toggleButtons].forEach(function(button) {

      let content = ((toggleContentElement === null) ? button.nextElementSibling : toggleContentElement);

      if (content.classList.contains('js-toggle-content-only-mobile')) {
        if (checkMediaQuery() == breakpoints.medium) {
          content.removeAttribute('aria-hidden');
        }
      }
      // Add click event
      button.addEventListener("click", function() {
        toggleContentVisibility(button, content);
      });
    });
  } else {
    let content = ((toggleContentElement === null) ? toggleButtons.nextElementSibling : toggleContentElement);

    if (content.classList.contains('js-toggle-content-only-mobile')) {
      if (checkMediaQuery() == breakpoints.medium) {
        content.removeAttribute('aria-hidden');
      }
    }
    toggleButtons.setAttribute('role', 'button');

    // Add click event
    toggleButtons.addEventListener("click", function() {
      toggleContentVisibility(toggleButtons, content);
    });
  }

}

module.exports = toggleContent;
