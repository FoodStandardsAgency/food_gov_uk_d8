<?php

namespace Drupal\fsa_es\Plugin\ElasticsearchQueryBuilder;

use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\fsa_es\SearchService;
use Elasticsearch\Client;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @ElasticsearchQueryBuilder(
 *   id = "sitewide_search_ratings",
 *   label = @Translation("Global search: Ratings"),
 *   description = @Translation("Provides query builder for site-wide ratings search.")
 * )
 */
class SitewideSearchRatings extends SitewideSearchBase {

  /**
   * @var \Drupal\fsa_es\SearchService
   */
  protected $ratingsSearchService;

  /**
   * @var null
   */
  protected $aggregations = NULL;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, LanguageManagerInterface $language_manager, Client $elasticsearch_client, SearchService $ratings_search_service) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $language_manager, $elasticsearch_client);

    $this->ratingsSearchService = $ratings_search_service;
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
      $container->get('elasticsearch_helper.elasticsearch_client'),
      $container->get('fsa_es.search_service')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildQuery() {
    // Get query plugin.
    $query_plugin = $this->view->getQuery();

    // Get filter values.
    $values = $this->getFilterValues();

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
        $filters[$filter_key] = implode(',', array_filter($values[$filter_value]));
      }
    }

    // Get query.
    $query = $this->ratingsSearchService->buildQuery($this->currentLanguage, $keyword, $filters, $query_plugin->getLimit(), $query_plugin->offset);

    return $query;
  }

  /**
   * Returns rating aggregations.
   *
   * @return array
   *   Array of rating aggregations.
   */
  public function getAggregations() {
    if (!is_array($this->aggregations)) {
      $this->aggregations = $this->ratingsSearchService->categories($this->currentLanguage);
    }

    return $this->aggregations;
  }

  /**
   * Returns a list of business types.
   *
   * @return array
   *   Array of business types.
   */
  public function getBusinessTypeFilterOptions() {
    $aggregations = $this->getAggregations();
    return $this->ratingsSearchService->aggsToOptions($aggregations['business_types']);
  }

  /**
   * Returns a list of local authorities.
   *
   * @return array
   *   Array of local authorities.
   */
  public function getLocalAuthorityFilterOptions() {
    $aggregations = $this->getAggregations();
    return $this->ratingsSearchService->aggsToOptions($aggregations['local_authorities']);
  }

  /**
   * Returns a list of hygiene rating values for England, Northern Ireland, Wales.
   *
   * @return array
   *   Array of hygiene rating values for England, Northern Ireland, Wales.
   */
  public function getFhrsRatingValueFilterOptions() {
    $aggregations = $this->getAggregations();
    return $this->ratingsSearchService->defineAndSortArrayItems(
      $this->ratingsSearchService->aggsToOptions($aggregations['fhrs_rating_values']),
      [
        5,
        4,
        3,
        2,
        1,
        0,
        'AwaitingPublication',
        'AwaitingInspection',
        'Exempt',
      ]
    );
  }

  /**
   * Returns a list of hygiene rating values for Scotland.
   *
   * @return array
   *   Array of hygiene rating values for Scotland.
   */
  public function getFhisRatingValueFilterOptions() {
    $aggregations = $this->getAggregations();
    return $this->ratingsSearchService->defineAndSortArrayItems(
      $this->ratingsSearchService->aggsToOptions($aggregations['fhis_rating_values']),
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
