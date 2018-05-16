import checkMediaQuery from '../../helper/checkMediaQuery'
import state from '../../helper/toggleHelpers'

function autoOpenFirstSearchFilter () {
  // Check if desktop size (ie. not 'xs' or 's')
  if (checkMediaQuery().includes('s')) return

  // Attempt to get the first search filter (button and content)
  const firstFilterButton = document.querySelector('legend.toggle-fieldset__button')
  const firstFilterContent = document.querySelector('div.toggle-fieldset__content')
  if (!firstFilterButton || !firstFilterContent) return

  // Set filter button and filter content to open
  state.on({ element: firstFilterButton, type: 'button' }, 'is-open')
  state.on({ element: firstFilterContent, type: 'content' }, 'is-open')
}

module.exports = autoOpenFirstSearchFilter
