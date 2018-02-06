<?php

namespace Drupal\fsa_es\Plugin\ElasticsearchQueryBuilder;

use Drupal\fsa_es\SearchService;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @ElasticsearchQueryBuilder(
 *   id = "sitewide_search_all",
 *   label = @Translation("Global search: All"),
 *   description = @Translation("Provides query builder for site-wide global search.")
 * )
 */
class SitewideSearchAll extends SitewideSearchBase {

  /** @var \Drupal\fsa_es\SearchService $ratingsSearchService */
  protected $ratingsSearchService;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('language_manager'),
      $container->get('elasticsearch_helper.elasticsearch_client'),
      $container->get('request_stack')
    );

    // Set ratings search service instance.
    $instance->setRatingsSearchService($container->get('fsa_es.search_service'));

    return $instance;
  }

  /**
   * Sets ratings search service instance.
   *
   * @param \Drupal\fsa_es\SearchService $ratings_search_service
   */
  public function setRatingsSearchService(SearchService $ratings_search_service) {
    $this->ratingsSearchService = $ratings_search_service;
  }

  /**
   * Builds Elasticsearch base query.
   *
   * @return array
   */
  public function buildBaseQuery() {
    // Get filter values.
    $values = $this->getFilterValues();

    $query_must_filters = [];
    $query_filter_filters = [];

    $query = [
      'index' => $this->getIndices(),
      'body' => [],
    ];

    // Apply the filters to the query.
    if (!empty($values['keyword'])) {
      $query_must_filters[] = [
        'multi_match' => [
          'query' => $values['keyword'],
          'fields' => ['name^3', 'body'],
          'type' => 'cross_fields',
          'operator' => 'and',
        ],
      ];
    }
    else {
      // Sort by updated if no keywords are given.
      $query['body']['sort'] = ['updated' => 'desc'];
    }

    // Assign the text search filters to the query in the 'must' section.
    foreach ($query_must_filters as $filter) {
      $query['body']['query']['bool']['must'][] = $filter;
    }

    // Assign the text search filters to the query in the 'filter' section.
    foreach ($query_filter_filters as $filter) {
      $query['body']['query']['bool']['filter'][] = $filter;
    }

    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function buildQuery() {
    // Get the base query.
    $query = $this->buildBaseQuery();

    return $query;
  }

  /**
   * Returns a list of indices that search should be performed on.
   *
   * @return array
   */
  protected function getIndices() {
    $langcode = $this->currentLanguage->getId();

    return [
      'alert',
      'consultation-' . $langcode,
      'news-' . $langcode,
      'page-' . $langcode,
      'research-' . $langcode,
    ];
  }

}
