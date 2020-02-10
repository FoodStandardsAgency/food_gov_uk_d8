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

function hideSearchFiltersEmptyResults () {
  // Check if search page.
  const searchPage = document.querySelector('.view-news-alerts-search')
  if (!searchPage) return

  // Check whether view has output results.
  const hasResults = document.querySelector('.view-news-alerts-search .views-row')
  if (hasResults) return

  const sidebar = document.querySelector('.layout__sidebar')
  const mainContent = document.querySelector('.layout--main.layout--with-sidebar')

  if (!sidebar || !mainContent) return

  // Hide sidebar when no view rows output found on search page.
  sidebar.style.display = 'none';

  // Ensure empty message is displayed as full width within container.
  mainContent.classList.remove('layout--with-sidebar');
}

module.exports = autoOpenFirstSearchFilter
module.exports = hideSearchFiltersEmptyResults
