import state from '../../helper/toggleHelpers'

function changeAriaRoleOnToggle () {

  // All the toggle buttons inside toggle legends
  const toggleButtons = [...document.querySelectorAll('.toggle-fieldset__button .fieldset-legend')]

  toggleButtons.forEach((toggleButton) => {
    toggleButton.addEventListener('click', function () {
      const legendCurrent = element.closest('.toggle-fieldset__button')
      if (legendCurrent.getAttribute('aria-expanded') === 'false') {
        legendCurrent.setAttribute('aria-expanded', 'true')
      } else {
        legendCurrent.setAttribute('aria-expanded', 'false')
      }
    }, true)
  })
}

function autoOpenFormError () {
  // If there's an error, open the content where the error is
  const profileManager = document.querySelector('#profile-manager')
  if (!profileManager) return

  const problemElement = profileManager.querySelector('input.error')
  if (!problemElement) return

  const toggleContent = problemElement.closest('.toggle-content')
  const toggleButton = toggleContent.previousElementSibling

  state.on({ element: toggleContent, type: 'content' }, 'is-open')
  state.on({ element: toggleButton, type: 'button' }, 'is-open')
}

function scrollToMultiStepForm () {
  // If user is completing a form with multiple steps, autoscroll to each step

  // Check if user is halfway through completing a multi-step form
  const webform = document.querySelector('.webform-submission-form')
  if (!webform || !webform.querySelector('input[value="Previous"]')) return

  // Focus the first form input
  const firstInput = webform.querySelector('input, textarea, select')
  if (firstInput) {
    firstInput.focus({'preventScroll': true})

    // Scroll to the form element
    window.scrollTo({
      top: webform.getBoundingClientRect().top + window.scrollY,
      behavior: 'smooth'
    })
  }
}

module.exports = { changeAriaRoleOnToggle, autoOpenFormError, scrollToMultiStepForm }
