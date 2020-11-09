<?php

namespace Drupal\fsa_es\Plugin\ElasticsearchQueryBuilder;

/**
 * @ElasticsearchQueryBuilder(
 *   id = "sitewide_search_research",
 *   label = @Translation("Global search: Research and Evidence"),
 *   description = @Translation("Provides query builder for site-wide guidance search.")
 * )
 */
class SitewideSearchResearch extends SitewideSearchBase {

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
      'index' => [
        'research-' . $this->currentLanguage->getId(),
        'evidence-' . $this->currentLanguage->getId(),
      ],
      'body' => [],
    ];

    // Apply the filters to the query.
    if (!empty($values['keyword'])) {
      $query_must_filters[] = [
        'multi_match' => [
          'query' => $values['keyword'],
          'fields' => ['name^5', 'intro^3', 'body'],
          'type' => 'cross_fields',
          'operator' => 'and',
        ],
      ];
    }
    else {
      // Sort by updated if no keywords are given.
      $query['body']['sort'] = ['updated' => 'desc'];
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

    if (!empty($values['evidence_type'])) {
      $query_filter_filters[] = [
        'terms' => [
          'evidence_type.label.keyword' => array_filter(array_values($values['evidence_type'])),
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
   *   Array of rating aggregations.
   */
  public function getAggregations() {
    if (!is_array($this->aggregations)) {
      $query = [
        'index' => [
          'research-' . $this->currentLanguage->getId(),
          'evidence-' . $this->currentLanguage->getId(),
        ],
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
            'evidence_type' => [
              'terms' => [
                'field' => 'evidence_type.label.keyword',
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
        'evidence_type'   => $result['aggregations']['evidence_type']['buckets'],
      ];
    }

    return $this->aggregations;
  }

  /**
   * Returns a list of topics.
   *
   * @return array
   *   Array of topics.
   */
  public function getTopicFilterOptions() {
    $aggregations = $this->getAggregations();

    return $this->aggsToOptions($aggregations['topics']);
  }

  /**
   * Returns a list of evidence types.
   *
   * @return array
   *   Array of evidence types.
   */
  public function getEvidenceTypeFilterOptions() {
    $aggregations = $this->getAggregations();

    return $this->aggsToOptions($aggregations['evidence_type']);
  }
}
