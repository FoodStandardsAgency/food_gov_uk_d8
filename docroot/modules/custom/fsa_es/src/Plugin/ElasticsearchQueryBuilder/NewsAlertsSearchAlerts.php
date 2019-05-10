<?php

namespace Drupal\fsa_es\Plugin\ElasticsearchQueryBuilder;

/**
 * @ElasticsearchQueryBuilder(
 *   id = "news_alerts_search_alerts",
 *   label = @Translation("News and alerts search: Alerts"),
 *   description = @Translation("Provides query builder for alerts search.")
 * )
 */
class NewsAlertsSearchAlerts extends SitewideSearchBase {

  /**
   * @var null
   */
  protected $aggregations = NULL;

  /**
   * Builds Elasticsearch base query.
   *
   * @return array
   *   Query array.
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
      // Sort by created if no keywords are given.
      $query['body']['sort'] = ['created' => 'desc'];
    }

    // Add news type as a term filter.
    if (!empty($values['news_type'])) {
      $query_filter_filters[] = [
        'terms' => [
          'news_type' => array_filter(array_values($values['news_type'])),
        ],
      ];
    }

    // Region is only applicable to news and alerts.
    if (!empty($values['nation'])) {
      $query_filter_filters[] = [
        'terms' => [
          'nation.label.keyword' => array_filter(array_values($values['nation'])),
        ],
      ];
    }

    if ($query_must_filters) {
      $query['body']['query']['bool']['must'] = $query_must_filters;
    }

    if ($query_filter_filters) {
      $query['body']['query']['bool']['filter'] = $query_filter_filters;
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
   *   Array of indices a search should operate on.
   */
  protected function getIndices() {
    return [
      'alert',
    ];
  }

  /**
   * Returns rating aggregations.
   *
   * @return array
   *   Array of rating aggregations.
   */
  public function getAggregations() {
    if (!is_array($this->aggregations)) {
      $query = [
        'index' => $this->getIndices(),
        'size' => 0,
        'body' => [
          'aggs' => [
            'type' => [
              'terms' => [
                'field' => 'news_type.keyword',
                'order' => ['_term' => 'asc'],
                'size' => 10000,
              ],
            ],
            'nation' => [
              'terms' => [
                'field' => 'nation.label.keyword',
                'order' => ['_term' => 'asc'],
                'size' => 10000,
              ],
            ],
          ],
        ],
      ];

      // Execute the query.
      $result = $this->elasticsearchClient->search($query);

      // Build the response.
      $this->aggregations = [
        'type' => $result['aggregations']['type']['buckets'],
        'nation' => $result['aggregations']['nation']['buckets'],
      ];
    }

    return $this->aggregations;
  }

  /**
   * Returns a list of news and alert types.
   *
   * @return array
   *   News and alert types
   */
  public function getNewsTypeFilterOptions() {
    $aggregations = $this->getAggregations();

    return $this->defineAndSortArrayItems(
      $this->aggsToOptions($aggregations['type']),
      [
        (string) $this->t('Allergy alert'),
        (string) $this->t('Food alert'),
      ]
    );
  }

}
