<?php

namespace Drupal\fsa_es\Plugin\ElasticsearchQueryBuilder;

/**
 * @ElasticsearchQueryBuilder(
 *   id = "sitewide_search_guidance",
 *   label = @Translation("Global search: Guidance"),
 *   description = @Translation("Provides query builder for site-wide guidance search.")
 * )
 */
class SitewideSearchGuidance extends SitewideSearchBase {

  /** @var null|array $aggregations */
  protected $aggregations = NULL;

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
      'index' => ['page-' . $this->currentLanguage->getId()],
      'body' => [],
    ];

    // Filter by content type.
    if (!empty($values['guidance_content_type'])) {
      $query_filter_filters[] = [
        'terms' => [
          'content_type.id' => array_values($values['guidance_content_type']),
        ],
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

    if (!empty($values['guidance_audience'])) {
      $query_filter_filters[] = [
        'terms' => [
          'audience.label.keyword' => array_filter(array_values($values['guidance_audience'])),
        ],
      ];
    }

    if (!empty($values['nation'])) {
      $query_filter_filters[] = [
        'terms' => [
          'nation.label.keyword' => array_filter(array_values($values['nation'])),
        ],
      ];
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
   * Returns rating aggregations.
   *
   * @return array
   */
  public function getAggregations() {
    if (!is_array($this->aggregations)) {
      // Get filter values.
      $values = $this->getFilterValues($this->view);

      $query = [
        'index' => ['page-' . $this->currentLanguage->getId()],
        'size' => 0,
        'body' => [
          'aggs' => [
            'audience' => [
              'terms' => [
                'field' => 'audience.label.keyword',
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

      // Filter by content type.
      if (!empty($values['guidance_content_type'])) {
        $query['body']['query']['bool']['must'][] = [
          'terms' => [
            'content_type.id' => array_values($values['guidance_content_type']),
          ],
        ];
      }

      // Execute the query.
      $result = $this->elasticsearchClient->search($query);

      // Build the response.
      $this->aggregations = [
        'audience' => $result['aggregations']['audience']['buckets'],
        'nation' => $result['aggregations']['nation']['buckets'],
      ];
    }

    return $this->aggregations;
  }

  /**
   * Returns a list of audiences.
   *
   * @return array
   */
  public function getAudienceFilterOptions() {
    $aggregations = $this->getAggregations();

    return $this->aggsToOptions($aggregations['audience']);
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
