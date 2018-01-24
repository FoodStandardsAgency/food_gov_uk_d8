<?php

namespace Drupal\fsa_es\Plugin\ElasticsearchQueryBuilder;

use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\fsa_es\SearchService;
use Drupal\views\ViewExecutable;
use Elasticsearch\Client;
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
   *
   * @param \Drupal\fsa_es\SearchService $ratings_search_service
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
   * Builds Elasticsearch base query.
   *
   * @return array
   */
  public function buildBaseQuery() {
    // Get filter values.
    $values = $this->getFilterValues($this->view);

    // Prepare query.
    $query = [];
    // Prepare should filters.
    $query_should_filters = [];

    // Create should query for each index.
    foreach ($this->getIndices() as $index_group => $indices) {
      // Each should index will contain a set of must filters.
      $index_bool_query = [];

      // For ratings the query is retrieved from an external ratings search
      // service.
      if ($index_group == 'ratings') {
        $ratings_query = $this->ratingsSearchService->buildQuery($this->currentLanguage, $values['keyword']);

        if (isset($ratings_query)) {
          $index_bool_query = array_merge($index_bool_query, $ratings_query['body']['query']['bool']);
          // Remove all should clauses so that they do not influence the score.
          unset($index_bool_query['should']);
        }
      }
      // For other indices a simple keyword query is added.
      else {
        // Apply the filters to the query.
        if (!empty($values['keyword'])) {
          $index_bool_query['must'][] = [
            'multi_match' => [
              'query' => $values['keyword'],
              'fields' => ['name^3', 'body'],
              'type' => 'cross_fields',
              'operator' => 'and',
            ],
          ];
        }
      }

      // Add index as a term filter.
      $index_bool_query['filter'][]['terms']['_index'] = $indices;

      // Add a set of must filters to the should query.
      $query_should_filters[]['bool'] = $index_bool_query;
    }

    // Assign the filters to the query in the 'should' section.
    foreach ($query_should_filters as $filter) {
      $query['body']['query']['bool']['should'][] = $filter;
    }
    // At least one "should" query must be matched.
    $query['body']['query']['bool']['minimum_should_match'] = 1;

    // Sort by updated if no keywords are given.
    if (empty($values['keyword'])) {
      $query['body']['sort'][] = [
        'updated' => [
          'order' => 'desc',
          'unmapped_type' => 'date',
        ],
      ];
    }

    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function buildQuery(ViewExecutable $view) {
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
      'all' => [
        'alert',
        'consultation-' . $langcode,
        'news-' . $langcode,
        'page-' . $langcode,
        'research-' . $langcode,
      ],
      'ratings' => [
        'ratings-' . $langcode
      ],
    ];
  }

}
