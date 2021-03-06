<?php

namespace Drupal\fsa_es\Plugin\ElasticsearchQueryBuilder;

/**
 * @ElasticsearchQueryBuilder(
 *   id = "sitewide_search_news_alerts",
 *   label = @Translation("Global search: News and alerts"),
 *   description = @Translation("Provides query builder for site-wide news and alerts search.")
 * )
 */
class SitewideSearchNewsAlerts extends SitewideSearchBase {

  /**
   * @var null
   */
  protected $aggregations = NULL;

  /**
   * Builds Elasticsearch base query.
   *
   * @return array
   *   Elasticsearch base query
   */
  public function buildBaseQuery() {
    $langcode = $this->currentLanguage->getId();

    // Get filter values.
    $values = $this->getFilterValues();

    // Prepare query.
    $query = [];
    $query_should_filters = [];

    // Prepare news types.
    $news_types = !empty($values['news_type']) ? array_filter($values['news_type']) : [];

    // Get indices.
    $indices = $this->getIndices($news_types);

    // Get news type => index mapping.
    $type_index_mapping = $this->getIndexNewsTypeMapping();

    // Create should query for each index.
    foreach ($indices as $index) {
      // Each should index will contain a set of must filters.
      $index_must_filter = [];
      $index_filter_filter = [];

      // Add index as a term filter.
      $index_filter_filter[] = [
        'term' => [
          '_index' => $index,
        ],
      ];

      // Add keyword as a text query filter.
      if (!empty($values['keyword'])) {
        $index_must_filter[] = [
          'multi_match' => [
            'query' => $values['keyword'],
            'fields' => ['name^3', 'body'],
            'type' => 'cross_fields',
            'operator' => 'and',
          ],
        ];
      }

      // Add news type as a term filter.
      if (!empty($news_types)) {
        $index_news_types = array_values(array_intersect($news_types, $type_index_mapping[$index]));

        $index_filter_filter[] = [
          'terms' => [
            'news_type' => $index_news_types,
          ],
        ];
      }

      // Region is only applicable to news and alerts.
      if (!empty($values['nation']) && in_array($index, ['alert', 'news-' . $langcode])) {
        $index_filter_filter[] = [
          'terms' => [
            'nation.label.keyword' => array_filter(array_values($values['nation'])),
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

        $index_filter_filter[] = [
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

        $index_filter_filter[] = [
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

        $index_must_filter[] = $year_bool_query;
      }

      // Create a bool query.
      $bool_query = [
        'must' => $index_must_filter,
        'filter' => $index_filter_filter,
      ];

      $query_should_filters[]['bool'] = $bool_query;
    }

    // Assign the filters to the query in the 'should' section.
    foreach ($query_should_filters as $filter) {
      $query['body']['query']['bool']['should'][] = $filter;
    }
    // At least one "should" query must be matched.
    $query['body']['query']['bool']['minimum_should_match'] = 1;

    // Sort by created if no keywords are given.
    if (empty($values['keyword'])) {
      $query['body']['sort'][] = [
        'created' => [
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
  public function buildQuery() {
    // Get the base query.
    $query = $this->buildBaseQuery();

    return $query;
  }

  /**
   * Returns index/news type mapping.
   *
   * @return array
   *   Index/news type mapping
   */
  protected function getIndexNewsTypeMapping() {
    $langcode = $this->currentLanguage->getId();

    return [
      'alert' => [
        (string) $this->t('Allergy alert'),
        (string) $this->t('Food alert'),
      ],
      'news-' . $langcode => [
        (string) $this->t('News'),
      ],
      'consultation-' . $langcode => [
        (string) $this->t('Consultation'),
        (string) $this->t('Help shape our policies'),
        (string) $this->t('Rapidly developing policies'),
      ],
    ];
  }

  /**
   * Returns a list of indices that search should be performed on.
   *
   * @param array $news_types
   *   Array of news types.
   *
   * @return array
   *   Unique indices.
   */
  protected function getIndices(array $news_types = []) {
    // Get news type => index mapping.
    $type_index_mapping = $this->getIndexNewsTypeMapping();

    if (empty($news_types)) {
      $indices = array_keys($type_index_mapping);
    }
    else {
      $indices = [];

      foreach ($type_index_mapping as $index => $types) {
        if (array_intersect($news_types, $types)) {
          $indices[] = $index;
        }
      }
    }

    // Return unique indices.
    return array_unique($indices);
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
   *   Array of news and alert types.
   */
  public function getNewsTypeFilterOptions() {
    $aggregations = $this->getAggregations();

    return $this->defineAndSortArrayItems(
      $this->aggsToOptions($aggregations['type']),
      [
        (string) $this->t('Allergy alert'),
        (string) $this->t('Food alert'),
        (string) $this->t('News'),
        (string) $this->t('Consultation'),
        (string) $this->t('Help shape our policies'),
        (string) $this->t('Rapidly developing policies'),
      ]
    );
  }

}
