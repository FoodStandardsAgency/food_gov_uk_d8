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
    return [
      'body' => [
        'query' => [
          'match_all' => new \stdClass(),
        ],
      ],
    ];
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
    return $this->searchService->aggsToOptions($aggregations['fhrs_rating_values']);
  }

  /**
   * Returns a list of hygiene rating values for Scotland.
   *
   * @return array
   */
  public function getFhisRatingValueFilterOptions() {
    $aggregations = $this->getAggregations();
    return $this->searchService->aggsToOptions($aggregations['fhis_rating_values']);
  }

}
