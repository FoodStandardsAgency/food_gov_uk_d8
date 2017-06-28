function toggleContent(toggleButtons) {
  const toggleContentVisibility = (button) => {
    const content = button.nextElementSibling;
    if (content.classList.contains('is-open')) {
      content.classList.remove('is-open');
      button.classList.remove('is-open');
    } else {
      content.classList.add('is-open');
      button.classList.add('is-open');
    }
  }

  toggleButtons.forEach(function(button) {
    // Add click event
    button.addEventListener("click", function() {
      toggleContentVisibility(button);
    });
  });
}

module.exports = toggleContent;
