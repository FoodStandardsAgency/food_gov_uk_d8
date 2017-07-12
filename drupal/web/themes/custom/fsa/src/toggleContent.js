import checkMediaQuery from './checkMediaQuery';

function toggleContent(toggleButtons, breakpoints) {

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

  toggleButtons.forEach(function(button) {
    let content = button.nextElementSibling;

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
}

module.exports = toggleContent;
