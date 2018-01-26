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

    // Add news type as a term filter.
    if (!empty($values['consultation_status'])) {
      // Selected checkbox values come as strings, so "0" would mean that
      // value of "0" is selected, while 0 would mean that it was not selected.
      $filtered_status_values = array_filter($values['consultation_status'], function($item) {
        return is_string($item);
      });

      $query_filter_filters[] = [
        'terms' => [
          'status' => array_map(function($item) {
            return (bool) $item;
          }, array_values($filtered_status_values)),
        ],
      ];
    }

    // Region is only applicable to news and alerts.
    if (!empty($values['consultation_responses_published'])) {
      // Selected checkbox values come as strings, so "0" would mean that
      // value of "0" is selected, while 0 would mean that it was not selected.
      $filtered_responses_published_values = array_filter($values['consultation_responses_published'], function($item) {
        return is_string($item);
      });

      $query_filter_filters[] = [
        'terms' => [
          'responses_published' => array_map(function($item) {
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
            'status' => [
              'terms' => [
                'field' => 'status',
                'order' => ['_term' => 'asc'],
                'size' => 10000,
              ],
            ],
            'responses_published' => [
              'terms' => [
                'field' => 'responses_published',
                'order' => ['_term' => 'asc'],
                'size' => 10000,
              ],
            ],
            'start_date' => [
              'date_histogram' => [
                'field' => 'consultation_start_date',
                'interval' => 'year',
                'format' => 'yyy',
              ],
            ],
            'close_date' => [
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
        'status' => $result['aggregations']['status']['buckets'],
        'responses_published' => $result['aggregations']['responses_published']['buckets'],
        'start_date' => $result['aggregations']['start_date']['buckets'],
        'close_date' => $result['aggregations']['close_date']['buckets'],
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

  /**
   * Returns a list of consultation statuses.
   *
   * @return array
   */
  public function getConsultationStatusFilterOptions() {
    $aggregations = $this->getAggregations();

    // Define human readable values.
    $human_readable_values = [
      1 => $this->t('Open'),
      0 => $this->t('Closed'),
    ];

    // Get aggregated values.
    $agg_values = array_column($aggregations['status'], 'key');

    // Return aggregated values with human readable values.
    return array_intersect_key($human_readable_values, array_combine($agg_values, $agg_values));
  }

  /**
   * Returns a filter for bool if responses are published.
   *
   * @return array
   */
  public function getConsultationResponsesPublishedFilterOptions() {
    $aggregations = $this->getAggregations();

    // Define human readable values.
    $human_readable_values = [
      1 => $this->t('Responses published'),
    ];

    // Get aggregated values.
    $agg_values = array_column($aggregations['responses_published'], 'key');

    // Return aggregated values with human readable values.
    return array_intersect_key($human_readable_values, array_combine($agg_values, $agg_values));
  }

  /**
   * Returns a filter for year.
   *
   * @return array
   */
  public function getConsultationYearFilterOptions() {
    $aggregations = $this->getAggregations();

    // Get aggregated values.
    $start_date_values = array_column($aggregations['start_date'], 'key_as_string');
    $close_date_values = array_column($aggregations['close_date'], 'key_as_string');
    $merged_values = array_unique(array_merge($start_date_values, $close_date_values));
    sort($merged_values);

    return array_combine($merged_values, $merged_values);

  }

}
