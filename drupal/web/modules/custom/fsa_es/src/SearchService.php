<?php

namespace Drupal\fsa_es;

use Drupal\Component\Utility\Html;
use Elasticsearch\Client;

/**
 * Class SearchService.
 *
 * @package Drupal\fsa_es
 */
class SearchService {

  const DEFAULT_MAX_RESULT_ITEMS = 100;
  const SEARCHABLE_FIELDS = [
    'name^5',
    'localauthoritycode.label.keyword^2',
    'address',
    'postcode',
  ];

  /** @var Client */
  private $client;

  /**
   * Constructs a new SearchService object.
   */
  public function __construct(Client $client) {
    $this->client = $client;
  }

  public function search($input = '', $filters = [], $max_items = self::DEFAULT_MAX_RESULT_ITEMS) {
    // Sanitize the input.
    $input = Html::escape($input);
    $query_must_filters = [];
    $query_should_filters = [];

    // Build the query
    $query = $base_query = [
      'index' => ['ratings'],
      'size' => $max_items,
      'body' => [
        'query' => [
          'bool' => [
            'must' => [
              ['term' => ['_type' => 'establishment']],
            ],
          ],
        ],
        // Aggregations needed for the potential facet filters
        'aggs' => [
          'business_types' => [
            'terms' => [
              'field' => 'businesstype.label.keyword'
            ],
          ],
          'local_authorities' => [
            'terms' => [
              'field' => 'localauthoritycode.label.keyword'
            ],
          ],
          'rating_values' => [
            'terms' => [
              'field' => 'ratingvalue.keyword'
            ],
          ],
        ],
      ]
    ];

    // Get sorting param from url.
    $sort = Html::escape(\Drupal::request()->query->get('sort'));

    // And pass sorting for ES.
    switch ($sort) {
      case 'ratings_asc':
        $query['body']['sort'] = ['ratingvalue.keyword' => 'asc'];
        break;
      case 'ratings_desc':
        $query['body']['sort'] = ['ratingvalue.keyword' => 'desc'];
        break;
      case 'name_asc':
        $query['body']['sort'] = ['name.keyword' => 'asc'];
        break;
      case 'name_desc':
        $query['body']['sort'] = ['name.keyword' => 'desc'];
        break;
    }

    // Apply the filters to the query:
    if (!empty($filters['business_type'])) {
      $ids = explode(',', $filters['business_type']);
      $query_must_filters[] = ['terms' => ['businesstype.label.keyword' => $ids]];
    }
    if (!empty($filters['local_authority'])) {
      $ids = explode(',', $filters['local_authority']);
      $query_must_filters[] = ['terms' => ['localauthoritycode.label.keyword' => $ids]];
    }
    if (isset($filters['rating_value'])) {
      $ids = explode(',', $filters['rating_value']);
      $query_must_filters[] = ['terms' => ['ratingvalue.keyword' => $ids]];
    }

    $base_query_should_filters = $query_should_filters;
    $base_query_must_filters = $query_must_filters;

    if (!empty($input)) {
      $query_must_filters[] = ['multi_match' => [
        'query' => $input,
        'fields' => self::SEARCHABLE_FIELDS,
        'operator' => 'and'
      ]];
      $query_should_filters[] = ['match_phrase' => [
        'name' => [
          'query' => $input,
          'slop' => 2,
          'boost' => 5,
        ],
      ]];
    }

    // Assign the term filters to the query in the 'must' section
    foreach ($query_must_filters as $f) {
      $query['body']['query']['bool']['must'][] = $f;
    }

    // Assign the term filters to the query in the 'should' section
    foreach ($query_should_filters as $f) {
      $query['body']['query']['bool']['should'][] = $f;
    }

    // Execute the query.
    $result = $this->client->search($query);


    // NO RESULTS FOUND:
    if ($result['hits']['total'] == 0 && !empty($input)) {
      // Reset the filtering to the base values
      $query = $base_query;
      $query_must_filters = $base_query_must_filters;
      $query_should_filters = $base_query_should_filters;

      // Assign looser settings to the multi match and match_phrase queries (with fuzziness)
      /*
      $query_must_filters[] = ['match_phrase' => [
        'combinedvalues' => [
          'query' => $input,
          'slop' => 3,
          'boost' => 5,
        ],
      ]];
      */
      $query_must_filters[] = ['match' => [
        'combinedvalues' => [
          'query' => $input,
          'fuzziness' => 'AUTO',
          'prefix_length' => 1, // Don't let the first letter be fuzzy.
          'operator' => 'and'
        ],
      ]];
      foreach ($query_must_filters as $f) {
        $query['body']['query']['bool']['must'][] = $f;
      }
      foreach ($query_should_filters as $f) {
        $query['body']['query']['bool']['should'][] = $f;
      }

      // Re-run the query
      $result = $this->client->search($query);
    }


    // Build the response
    $response = [
      'results' => [],
      'total' => $result['hits']['total'],
      'aggs' => [
        'business_types' => $result['aggregations']['business_types']['buckets'],
        'local_authorities' => $result['aggregations']['local_authorities']['buckets'],
        'rating_values' => $result['aggregations']['rating_values']['buckets'],
      ]
    ];

    foreach ($result['hits']['hits'] as $hit) {
      $source_data = $hit['_source'];
      $response['results'][] = $source_data;
    }

    return $response;
  }

  /**
   * Get the list of possible categories in a format suitable for Form API (select and checkboxes elements)
   *
   * @return array
   *  An associated array with the keys being the type of the category and the value suitable for Form API #options parameter.
   */
  public function categories() {

    // Define the base query
    $base_query = $query = [
      'index' => ['ratings'],
      'size' => 0,
      'body' => [
        'aggs' => [
          'business_types' => [
            'terms' => [
              'field' => 'businesstype.label.keyword',
              'order' => ['_term' => 'asc'],
              'size' => 10000,
            ],
          ],
          'local_authorities' => [
            'terms' => [
              'field' => 'localauthoritycode.label.keyword',
              'order' => ['_term' => 'asc'],
              'size' => 10000,
            ],
          ],
          'rating_values' => [
            'terms' => [
              'field' => 'ratingvalue.keyword',
              'order' => ['_term' => 'asc'],
              'size' => 10000,
            ],
          ],
        ],
      ]
    ];

    // Execute the query.
    $result = $this->client->search($query);

    // Build the response
    $response = [
      'business_types' => $result['aggregations']['business_types']['buckets'],
      'local_authorities' => $result['aggregations']['local_authorities']['buckets'],
      'rating_values' => $result['aggregations']['rating_values']['buckets'],
    ];

    return $response;
  }


}
