import state from '../../helper/toggleHelpers'

function form () {
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

module.exports = form
