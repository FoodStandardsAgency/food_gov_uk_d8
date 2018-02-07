<?php

namespace Drupal\fsa_ratings\Controller;

use Drupal\Component\Utility\Html;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Url;
use Drupal\fsa_es\SearchService;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Controller class for the ratings search.
 *
 * @package Drupal\fsa_ratings\Controller
 */
class RatingsSearch extends ControllerBase {

  // Number of initial search results items.
  const INITIAL_RESULTS_COUNT = 20;

  // Number of items to "Load more".
  const ADDITIONAL_LOAD_COUNT = 10;

  // Name of the query parameter for keywords.
  const KEYWORDS_QUERY_PARAM = 'keywords';

  /**
   * {@inheritdoc}
   *
   * @var \Drupal\fsa_es\SearchService */
  protected $searchService;

  /** @var \Drupal\Core\Language\LanguageManagerInterface $languageManager */
  protected $languageManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('fsa_es.search_service'),
      $container->get('language_manager')
    );
  }

  /**
   * RatingsSearch constructor.
   *
   * @param \Drupal\fsa_es\SearchService $searchService
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   */
  public function __construct(SearchService $searchService, LanguageManagerInterface $language_manager) {
    $this->searchService = $searchService;
    $this->languageManager = $language_manager;
  }

  /**
   * Static function to get search parameters from URL.
   *
   * @return array
   *   Array with appropriate search parameters.
   */
  public static function getSearchParameters() {
    $params = [];

    $params['language'] = \Drupal::languageManager()->getCurrentLanguage();
    $params['keywords'] = \Drupal::request()->query->get(self::KEYWORDS_QUERY_PARAM);

    $filters = [];
    $filter_param_names = [
      'local_authority',
      'business_type',
      'rating_value',
      'fhis_rating_value',
      'fhrs_rating_value',
    ];
    foreach ($filter_param_names as $opt) {
      $value = \Drupal::request()->query->get($opt);
      if (isset($value)) {
        $filters[$opt] = $value;
      }
    }

    $params['filters'] = $filters;

    return $params;

  }

  /**
   * Page callback for /hygiene-ratings.
   */
  public function ratingsSearch() {
    $items = [];
    $categories = [];
    $hits = 0;
    $language = $this->languageManager->getCurrentLanguage();
    $available_filters = $this->searchService->categories($language);

    // User provided search input.
    $keywords = \Drupal::request()->query->get(self::KEYWORDS_QUERY_PARAM);

    // User provided max item count. Hard-limit is 1000. Default is in constant.
    $max_items = \Drupal::request()->query->get('max');
    if (empty($max_items) || $max_items > 1000) {
      $max_items = RatingsSearch::INITIAL_RESULTS_COUNT;
    }

    // Get page number.
    $page = \Drupal::request()->query->get('page');
    // And calculate offset.
    $offset = $page * RatingsSearch::INITIAL_RESULTS_COUNT;

    $filters = [];
    // See if the following parameters are provided by the user and add to the
    // list of filters.
    $filter_param_names = [
      'local_authority',
      'business_type',
      'rating_value',
      'fhis_rating_value',
      'fhrs_rating_value',
    ];
    foreach ($filter_param_names as $opt) {
      $value = \Drupal::request()->query->get($opt);
      if (isset($value)) {
        $filters[$opt] = $value;
      }
    }

    // Fetch results from the service only when either the filter values or
    // keywords are given.
    $results = FALSE;
    if (!empty($keywords) || !empty($filters)) {
      // Execute the search using the SearchService.
      $results = $this->searchService->search($language, $keywords, $filters, $max_items, $offset);
      $items = $this->ratingSearchResults($results);
      $hits = $results['total'];

      // Init the pager.
      pager_default_initialize($hits, RatingsSearch::INITIAL_RESULTS_COUNT);

    }

    $sort_form = NULL;
    $ratings_info = NULL;
    if ($results) {
      $sort_form = \Drupal::formBuilder()->getForm('Drupal\fsa_ratings\Form\FsaRatingsSearchSortForm');
    }
    else {
      $fsa_ratings_config = $this->config('config.fsa_ratings');
      $ratings_info = ['#markup' => check_markup($fsa_ratings_config->get('ratings_info_content'), 'full_html')];
    }

    $hits_total = number_format($hits, 0, ',', ' ');

    if ($hits > 0) {
      $from = ($offset + 1);
      $to = count($items) + $offset;
      $showing_items = $from . '-' . $to;
      $pager_info = $this->t('Showing <span>@items</span> of <span>@total</span>', ['@items' => $showing_items, '@total' => $hits_total]);
    }
    else {
      $pager_info = FALSE;
    }

    // Build the renderable result page.
    $render = [];
    $render[] = [
      '#theme' => 'fsa_ratings_search_page',
      '#form' => \Drupal::formBuilder()->getForm('Drupal\fsa_ratings\Form\FsaRatingsSearchForm'),
      '#sort_form' => $sort_form,
      '#ratings_info_content' => $ratings_info,
    // Actual result items.
      '#items' => $items,
    // Aggregation results, list of categories of the result items.
      '#categories' => $categories,
    // Keywords given in the URL.
      '#keywords' => $keywords,
    // Meaningful params (which have content associated)
      '#available_filters' => $available_filters,
    // Filters given by the user and used for the querying.
      '#applied_filters' => $filters,
    // Pager information.
      '#pager_info' => $pager_info,
    // Total count of the results.
      '#hits_total' => $hits_total,
    // Item count to be shown now.
      '#hits_shown' => count($items),
      //'#load_more' => \Drupal::formBuilder()->getForm('Drupal\fsa_ratings\Form\FsaRatingsSearchLoadMore'),
    ];

    // Append the pager.
    $render[] = ['#type' => 'pager'];

    // Generate pages information.
    if ($pager_info) {
      $page_number = round((($to / count($items)))) - 1;
      $number_of_pages = round(($hits / count($items))) - 1;
      $pages = $page_number . '-' . $number_of_pages;
    }
    else {
      $pages = NULL;
    }

    // Attach data layer library and settings to page.
    $render[] = [
      '#attached' => [
        'library' => [
          'fsa_gtm/data_layer.hygiene_ratings',
        ],
        'drupalSettings' => [
          'fsa_ratings' => [
            'data_layer' => [
              'keywords' => $keywords,
              'filters' => $filters,
              'pages' => $pages,
              'hits' => $hits_total,
            ],
          ],
        ],
      ],
    ];
    return $render;
  }

  /**
   * Build themed search results.
   *
   * @param array $results
   *   The search results array.
   *
   * @return array
   *   Themed search items array.
   */
  public static function ratingSearchResults(array $results) {
    $items = [];
    foreach ($results['results'] as $result) {
      $rating_value = $result['ratingvalue'];
      $result['ratingvalue'] = [
        '#markup' => '<p class="ratingvalue"><span class="description">' . t('Rating:') . '</span> <span class="numeric">' . $rating_value . '</span></p>',
      ];

      // Raw ratingvalue for theming purposes.
      $result['ratingvalue_class'] = Html::cleanCssIdentifier('rating--' . strtolower($rating_value));

      // Get scheme type to create the badge(s).
      $scheme = $result['schemetype'];

      // Static text if righttoreply data exists.
      if ($result['righttoreply'] != '') {
        $result['righttoreply'] = t('Right to reply published');
      }

      if ($result['newratingpending'] == TRUE) {
        $result['newratingpending'] = t('Recently inspected. New rating to be published soon.');
      }

      // Use ratingvalue to get the badge.
      $result['ratingimage'] = RatingsHelper::ratingBadgeImageDisplay($rating_value, $scheme);

      // Format displayed date(s).
      $result['ratingdate'] = RatingsHelper::ratingsDate($result['ratingdate']);

      // Add the link to the entity view page (with search query params to
      // populate the search form).
      $url = Url::fromRoute('entity.fsa_establishment.canonical', ['fsa_establishment' => $result['id']]);

      // Get query params and remove the AJAX-added items.
      $query = \Drupal::request()->query->all();
      unset($query['ajax_form']);
      unset($query['_wrapper_format']);

      $url->setOptions(['query' => $query]);
      $result['url'] = $url;
      $items[] = [
        '#theme' => 'fsa_ratings_search_result_item',
        '#item' => $result,
      ];
    }

    return $items;
  }

}
