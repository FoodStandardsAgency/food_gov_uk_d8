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
    $langcode = $this->currentLanguage->getId();

    // Get filter values.
    $values = $this->getFilterValues($this->view);

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
   * Returns index/news type mapping.
   *
   * @return array
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
   *
   * @return array
   */
  protected function getIndices($news_types = []) {
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
        (string) $this->t('Allergy alert'),
        (string) $this->t('Food alert'),
        (string) $this->t('News'),
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
