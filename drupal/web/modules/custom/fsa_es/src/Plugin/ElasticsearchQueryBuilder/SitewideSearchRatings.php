<?php

namespace Drupal\fsa_es\Plugin\ElasticsearchQueryBuilder;

use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\fsa_es\SearchService;
use Drupal\views\ViewExecutable;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @ElasticsearchQueryBuilder(
 *   id = "sitewide_search_ratings",
 *   label = @Translation("Ratings"),
 *   description = @Translation("Provides query builder for site-wide ratings search.")
 * )
 */
class SitewideSearchRatings extends SitewideSearchBase {

  /** @var \Drupal\fsa_es\SearchService $searchService */
  protected $searchService;

  /** @var null|array $aggregations */
  protected $aggregations = NULL;

  /**
   * {@inheritdoc}
   *
   * @param \Drupal\fsa_es\SearchService $search_service
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, LanguageManagerInterface $language_manager, SearchService $search_service) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $language_manager);

    $this->searchService = $search_service;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('language_manager'),
      $container->get('fsa_es.search_service')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildQuery(ViewExecutable $view) {
    // Get query plugin.
    $query_plugin = $view->getQuery();

    // Get filter values.
    $values = $this->getFilterValues($view);

    // Get keyword.
    $keyword = $values['keyword'];
    $filters = [];

    $filter_map = [
      'business_type' => 'ratings_business_type',
      'local_authority' => 'ratings_local_authority',
      'fhrs_rating_value' => 'ratings_fhrs_rating_value',
      'fhis_rating_value' => 'ratings_fhis_rating_value',
    ];

    // Map filter keys to the keys mapped in $filter_map.
    foreach ($filter_map as $filter_key => $filter_value) {
      if (!empty($values[$filter_value])) {
        $filters[$filter_key] = join(',', $values[$filter_value]);
      }
    }

    // Get query.
    $query = $this->searchService->buildQuery($this->currentLanguage, $keyword, $filters, $query_plugin->getLimit(), $query_plugin->offset);

    return $query;
  }

  /**
   * Returns rating aggregations.
   *
   * @return array
   */
  public function getAggregations() {
    if (!is_array($this->aggregations)) {
      $this->aggregations = $this->searchService->categories($this->currentLanguage);
    }

    return $this->aggregations;
  }

  /**
   * Returns a list of business types.
   *
   * @return array
   */
  public function getBusinessTypeFilterOptions() {
    $aggregations = $this->getAggregations();
    return $this->searchService->aggsToOptions($aggregations['business_types']);
  }

  /**
   * Returns a list of local authorities.
   *
   * @return array
   */
  public function getLocalAuthorityFilterOptions() {
    $aggregations = $this->getAggregations();
    return $this->searchService->aggsToOptions($aggregations['local_authorities']);
  }

  /**
   * Returns a list of hygiene rating values for England, Northern Ireland, Wales.
   *
   * @return array
   */
  public function getFhrsRatingValueFilterOptions() {
    $aggregations = $this->getAggregations();
    return $this->searchService->defineAndSortArrayItems(
      $this->searchService->aggsToOptions($aggregations['fhrs_rating_values']),
      [
        5,
        4,
        3,
        2,
        1,
        0,
        'AwaitingInspection',
        'Exempt',
      ]
    );
  }

  /**
   * Returns a list of hygiene rating values for Scotland.
   *
   * @return array
   */
  public function getFhisRatingValueFilterOptions() {
    $aggregations = $this->getAggregations();
    return $this->searchService->defineAndSortArrayItems(
      $this->searchService->aggsToOptions($aggregations['fhis_rating_values']),
      [
        'Pass',
        'Pass and Eat Safe',
        'Improvement Required',
        'Awaiting Inspection',
        'Exempt',
      ]
    );
  }

}
