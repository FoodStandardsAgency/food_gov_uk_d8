<?php

namespace Drupal\fsa_ratings\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Drupal\fsa_es\SearchService;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Controller class for the ratings search.
 *
 * @package Drupal\fsa_ratings\Controller
 */
class RatingsSearch extends ControllerBase {

  /**
   * {@inheritdoc}
   *
   * @var \Drupal\fsa_es\SearchService*/
  private $searchService;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('fsa_es.search_service')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function __construct(SearchService $searchService) {
    $this->searchService = $searchService;
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
    $params['keywords'] = \Drupal::request()->query->get('q');

    $filters = [];
    $filter_param_names = ['local_authority', 'business_type', 'rating_value'];
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
   * Page callback for /ratings/search.
   */
  public function ratingsSearch() {
    $items = [];
    $categories = [];
    $hits = 0;
    $language = \Drupal::languageManager()->getCurrentLanguage();
    $available_filters = $this->searchService->categories($language);

    // User provided search input.
    $keywords = \Drupal::request()->query->get('q');

    // User provided max item count. Hard-limit is 1000. Default is 20.
    $max_items = \Drupal::request()->query->get('max');
    if (empty($max_items) || $max_items > 1000) {
      $max_items = 20;
    }

    $filters = [];
    // See if the following parameters are provided by the user and add to the
    // list of filters.
    $filter_param_names = ['local_authority', 'business_type', 'rating_value'];
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
      $results = $this->searchService->search($language, $keywords, $filters, $max_items);
      $items = $this->ratingSearchResults($results);
      $hits = $results['total'];
    }

    $sort_form = NULL;
    $ratings_info = NULL;
    if ($results) {
      $sort_form = \Drupal::formBuilder()->getForm('Drupal\fsa_ratings\Form\FsaRatingsSearchSortForm');
    }
    else {
      $fsa_ratings_config = $this->config('config.fsa_ratings');
      $ratings_info = ['#markup' => $fsa_ratings_config->get('ratings_info_content')];
    }

    return [
      '#theme' => 'fsa_ratings_search_page',
      '#form' => \Drupal::formBuilder()->getForm('Drupal\fsa_ratings\Form\FsaRatingsSearchForm'),
      '#sort_form' => $sort_form,
      '#ratings_info_content' => $ratings_info,
      '#items' => $items,                         // Actual result items
      '#categories' => $categories,               // Aggregation results, list of categories of the result items
      '#keywords' => $keywords,                   // Keywords given in the URL
      '#available_filters' => $available_filters, // Meaningful filters (which have content associated)
      '#applied_filters' => $filters,             // Filters given by the user and used for the querying
      '#hits_total' => $hits,                     // Total count of the results
      '#hits_shown' => count($items),             // Item count to be shown now
    ];
  }

  /**
   * Build themed search results.
   *
   * @param array $results
   *  The search results array.
   *
   * @return array
   *   Themed search items array.
   */
  public static function ratingSearchResults($results) {
    $items = [];
    foreach ($results['results'] as $result) {
      $rating_value = $result['ratingvalue'];
      $result['ratingvalue'] = [
        '#markup' => '<p class="ratingvalue"><span class="description">'. t('Rating:') .'</span> <span class="numeric">'. $rating_value .'</span></p>',
      ];

      // Get scheme from the establishment.
      // @todo: entity load can be bad for performance, consider getting the scheme from ES (would though need to be indexed first).
      $scheme = \Drupal::entityTypeManager()->getStorage('fsa_establishment')->load($result['id'])->get('field_schemetype')->getValue()[0]['value'];

      // Use ratingvalue to get the badge.
      $result['ratingimage'] = RatingsHelper::ratingBadgeImageDisplay($rating_value, $scheme);

      // Format displayed date(s).
      $result['ratingdate'] = RatingsHelper::ratingsDate($result['ratingdate']);

      // Add the link to the entity view page (with search query params to
      // populate the search form).
      $url = Url::fromRoute('entity.fsa_establishment.canonical', ['fsa_establishment' => $result['id']]);
      $url->setOptions(['query' => \Drupal::request()->query->all()]);
      $result['url'] = $url;
      $items[] = [
        '#theme' => 'fsa_ratings_search_result_item',
        '#item' => $result,
      ];
    }

    return $items;
  }

}
