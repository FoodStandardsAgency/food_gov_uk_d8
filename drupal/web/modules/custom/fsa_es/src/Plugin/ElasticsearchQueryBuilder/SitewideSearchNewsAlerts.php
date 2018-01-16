<?php

namespace Drupal\fsa_es\Plugin\ElasticsearchQueryBuilder;

use Drupal\views\ViewExecutable;

/**
 * @ElasticsearchQueryBuilder(
 *   id = "sitewide_search_news_alerts",
 *   label = @Translation("Global search: News and alerts"),
 *   description = @Translation("Provides query builder for site-wide news and alerts search.")
 * )
 */
class SitewideSearchNewsAlerts extends SitewideSearchBase {

  /** @var null|array $aggregations */
  protected $aggregations = NULL;

  /**
   * Builds Elasticsearch base query.
   *
   * @return array
   */
  public function buildBaseQuery() {
    // Get filter values.
    $values = $this->getFilterValues($this->view);

    $query_must_filters = [];

    $query = [
      'index' => $this->getIndices(),
      'body' => [],
    ];

    // Filter by content type.
    if (!empty($values['news_alerts_type'])) {
      $query_must_filters[] = [
        // 'terms' => [
        //   'content_type.id' => array_values($values['news_alerts_type']),
        // ],
      ];
    }

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

    if (!empty($values['nation'])) {
      $query_must_filters[] = [
        'terms' => [
          'nation.label.keyword' => array_filter(array_values($values['nation'])),
        ],
      ];
    }

    // Assign the term filters to the query in the 'must' section.
    foreach ($query_must_filters as $filter) {
      $query['body']['query']['bool']['must'][] = $filter;
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
    return [
      'news-' . $this->currentLanguage->getId(),
      'alert',
      // 'consultation-' . $this->currentLanguage->getId(),
    ];
  }

  /**
   * Returns rating aggregations.
   *
   * @return array
   */
  public function getAggregations() {
    if (!is_array($this->aggregations)) {
      $query = [
        'index' => $this->getIndices(),
        'size' => 0,
        'body' => [
          'aggs' => [
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
        'nation' => $result['aggregations']['nation']['buckets'],
      ];
    }

    return $this->aggregations;
  }

  /**
   * Returns a list of news and alert types.
   *
   * @return array
   */
  public function getNewsAlertsTypeFilterOptions() {
    return [
      'allergy_alert' => t('Allergy alert'),
      'food_alert' => t('Food alert'),
      'news' => t('News'),
      'consultation' => t('Consultation'),
      'help_share_policies' => $this->t('Help share our policies'),
    ];
  }

  /**
   * Returns a list of nations.
   *
   * @return array
   */
  public function getNationFilterOptions() {
    $aggregations = $this->getAggregations();

    return $this->aggsToOptions($aggregations['nation']);
  }

}
