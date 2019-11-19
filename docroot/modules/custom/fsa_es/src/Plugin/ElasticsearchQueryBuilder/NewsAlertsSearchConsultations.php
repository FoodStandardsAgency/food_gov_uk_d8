<?php

namespace Drupal\fsa_es\Plugin\ElasticsearchQueryBuilder;

/**
 * @ElasticsearchQueryBuilder(
 *   id = "news_alerts_search_consultations",
 *   label = @Translation("News and alerts search: Consultations"),
 *   description = @Translation("Provides query builder for alerts search.")
 * )
 */
class NewsAlertsSearchConsultations extends SitewideSearchBase {

  /**
   * @var null
   */
  protected $aggregations = NULL;

  /**
   * Builds Elasticsearch base query.
   *
   * @return array
   *   Elasticsearch base query.
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

    // Add news type as a term filter.
    if (!empty($values['consultation_status'])) {
      // Selected checkbox values come as strings, so "0" would mean that
      // value of "0" is selected, while 0 would mean that it was not selected.
      $filtered_status_values = array_filter($values['consultation_status'], function ($item) {
        return is_string($item);
      });

      $query_filter_filters[] = [
        'terms' => [
          'status' => array_map(function ($item) {
            return (bool) $item;
          }, array_values($filtered_status_values)),
        ],
      ];
    }

    // Region is only applicable to news and alerts.
    if (!empty($values['consultation_responses_published'])) {
      // Selected checkbox values come as strings, so "0" would mean that
      // value of "0" is selected, while 0 would mean that it was not selected.
      $filtered_responses_published_values = array_filter($values['consultation_responses_published'], function ($item) {
        return is_string($item);
      });

      $query_filter_filters[] = [
        'terms' => [
          'responses_published' => array_map(function ($item) {
            return (bool) $item;
          }, array_values($filtered_responses_published_values)),
        ],
      ];
    }

    // Apply year filter.
    if (!empty($values['consultation_year'])) {
      $year = reset($values['consultation_year']);
      // Range filtering should be performed on two date fields.
      $year_bool_query = [];

      foreach (['consultation_start_date', 'consultation_close_date'] as $field) {
        $year_bool_query['bool']['should'][] = [
          'range' => [
            $field => [
              'gte' => $year . '||/y',
              'lte' => $year . '||/y',
              'format' => 'yyyy',
              'time_zone' => DATETIME_STORAGE_TIMEZONE,
            ],
          ],
        ];
      }

      // At least one match in starting and closing date should be found.
      $year_bool_query['bool']['minimum_should_match'] = 1;

      $query_must_filters[] = $year_bool_query;
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
   *   An array of indices that search should be performed on.
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
   *   An array of rating aggregations.
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
            'consultation_status' => [
              'terms' => [
                'field' => 'status',
                'order' => ['_term' => 'asc'],
                'size' => 10000,
              ],
            ],
            'consultation_start_date' => [
              'date_histogram' => [
                'field' => 'consultation_start_date',
                'interval' => 'year',
                'format' => 'yyy',
              ],
            ],
            'consultation_close_date' => [
              'date_histogram' => [
                'field' => 'consultation_close_date',
                'interval' => 'year',
                'format' => 'yyy',
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
        'consultation_status' => $result['aggregations']['consultation_status']['buckets'],
        'consultation_start_date' => $result['aggregations']['consultation_start_date']['buckets'],
        'consultation_close_date' => $result['aggregations']['consultation_close_date']['buckets'],
        'nation' => $result['aggregations']['nation']['buckets'],
      ];
    }

    return $this->aggregations;
  }

  /**
   * Returns a list of news and alert types.
   *
   * @return array
   *   An array of news and alert types.
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
  }

}
