<?php

namespace Drupal\fsa_es\Plugin\ElasticsearchQueryBuilder;

/**
 * @ElasticsearchQueryBuilder(
 *   id = "sitewide_search_research",
 *   label = @Translation("Global search: Research"),
 *   description = @Translation("Provides query builder for site-wide guidance search.")
 * )
 */
class SitewideSearchResearch extends SitewideSearchBase {

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
      'index' => ['research-' . $this->currentLanguage->getId()],
      'body' => [],
    ];

    // Apply the filters to the query.
    if (!empty($values['keyword'])) {
      $query_must_filters[] = [
        'multi_match' => [
          'query' => $values['keyword'],
          'fields' => ['name^3', 'body', 'project_code'],
          'type' => 'cross_fields',
          'operator' => 'and',
        ],
      ];
    }
    else {
      // Sort by created if no keywords are given.
      $query['body']['sort'] = ['created' => 'desc'];
    }

    // Filter by content type.
    if (!empty($values['research_topic'])) {
      $query_filter_filters[] = [
        'terms' => [
          'topics.label.keyword' => array_filter(array_values($values['research_topic'])),
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
      $query = [
        'index' => ['research-' . $this->currentLanguage->getId()],
        'size' => 0,
        'body' => [
          'aggs' => [
            'topics' => [
              'terms' => [
                'field' => 'topics.label.keyword',
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
        'topics' => $result['aggregations']['topics']['buckets'],
        'nation' => $result['aggregations']['nation']['buckets'],
      ];
    }

    return $this->aggregations;
  }

  /**
   * Returns a list of topics.
   *
   * @return array
   */
  public function getTopicFilterOptions() {
    $aggregations = $this->getAggregations();

    return $this->aggsToOptions($aggregations['topics']);
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
