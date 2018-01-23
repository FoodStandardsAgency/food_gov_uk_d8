<?php

namespace Drupal\fsa_es\Plugin\ElasticsearchQueryBuilder;

use Drupal\views\ViewExecutable;

/**
 * @ElasticsearchQueryBuilder(
 *   id = "news_alerts_search_consultations",
 *   label = @Translation("News and alerts search: Consultations"),
 *   description = @Translation("Provides query builder for alerts search.")
 * )
 */
class NewsAlertsSearchConsultations extends SitewideSearchBase {

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
      'consultation-' . $langcode,
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
            'type' => [
              'terms' => [
                'field' => 'news_type',
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
   */
  public function getNewsTypeFilterOptions() {
    $aggregations = $this->getAggregations();

    return $this->defineAndSortArrayItems(
      $this->aggsToOptions($aggregations['type']),
      [
        (string) $this->t('Consultation'),
        (string) $this->t('Help shape our policies'),
        (string) $this->t('Rapidly developing policies'),
      ]
    );

    // This is more simple way to display options which is sorted by label.
    // $aggregations = $this->getAggregations();
    // return $this->aggsToOptions($aggregations['type']);
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
