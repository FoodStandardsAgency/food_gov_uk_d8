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
  const searchPage = document.querySelector('.view-news-alerts-search');
  const globalSearchPage = document.querySelector('.search-listing');
  if (!searchPage && !globalSearchPage) return;

  // Check whether view has output results.
  const hasResults = document.querySelector('.view-news-alerts-search .views-row');
  const hasGlobalResults = document.querySelector('.search-listing .views-row');
  if (hasResults || hasGlobalResults) return

  const sidebar = document.querySelector('.layout__sidebar')
  const mainContent = document.querySelector('.layout--main.layout--with-sidebar')

  if (!sidebar || !mainContent) return

  // Hide sidebar when no view rows output found on search page.
  sidebar.style.display = 'none';

  // Ensure empty message is displayed as full width within container.
  mainContent.classList.remove('layout--with-sidebar');
}

/**
 * Toggles scrollable sidebar on desktop for Research and Evidence search's
 * large amount of Topic filters.
 */
function toggleTopicFilterOverflow () {
  // Check if desktop size (ie. not 'xs' or 's')
  if (checkMediaQuery().includes('s')) return

  const topicLegend = document.querySelector('#edit-topic--2--wrapper legend');
  if (!topicLegend) {
    return;
  }

  topicLegend.addEventListener('click', function (e) {
    const aside = document.querySelector('aside.layout__sidebar.js-sticky-element');

    if (topicLegend.classList.contains('is-open')) {
      console.log(topicLegend.classList);
      aside.style.overflow = 'auto';
    }
    else {
      aside.style.overflow = 'hidden';
    }
  });
}

module.exports = autoOpenFirstSearchFilter
module.exports = hideSearchFiltersEmptyResults
module.exports = toggleTopicFilterOverflow
