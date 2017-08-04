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

  /** @var Client */
  private $client;

  /**
   * Constructs a new SearchService object.
   */
  public function __construct(Client $client) {
    $this->client = $client;
  }

  public function search($input = '', $filters = []) {
    // Sanitize the input.
    $input = Html::escape($input);
    $query_must_filters = [];
    $query_should_filters = [];

    // Define the base query
    $base_query = $query = [
      'index' => ['ratings'],
      'size' => 1000,
      'body' => [
        'query' => [
          'bool' => [
            'must' => [
              ['term' => ['_type' => 'establishment']],
            ],
          ],
        ],
        'aggs' => [
          'types' => [
            'terms' => [
              'field' => 'businesstype.label.keyword'
            ],
          ],
        ],
      ]
    ];


    if (!empty($input)) {
      $query_should_filters[] = ['match_phrase' => [
        'name' => [
          'query' => $input,
          'slop' => 2,
          'boost' => 5,
        ],
      ]];
      $query_should_filters[] = ['match' => [
        'name' => [
          'query' => $input,
          'fuzziness' => 'AUTO',
          'prefix_length' => 1, // Don't let the first letter be fuzzy.
        ],
      ]];
      $query_should_filters[] = ['multi_match' => [
        'query' => $input,
        'fields' => ['address', 'postcode', 'localauthoritycode.label.keyword'],
        'type' => 'phrase',
      ]];
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
    if (!empty($filters['rating_value'])) {
      $ids = explode(',', $filters['rating_value']);
      $query_must_filters[] = ['terms' => ['ratingvalue.keyword' => $ids]];
    }

    // Assign the term filters to the query in the 'must' section
    foreach ($query_must_filters as $f) {
      $query['body']['query']['bool']['must'][] = $f;
    }

    // Assign the term filters to the query in the 'should' section
    foreach ($query_should_filters as $f) {
      $query['body']['query']['bool']['should'][] = $f;
    }

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

    // Execute the query.
    $result = $this->client->search($query);

    // Build the response
    $response = [
      'results' => [],
      'total' => $result['hits']['total'],
      'types' => $result['aggregations']['types']['buckets'],
    ];

    foreach ($result['hits']['hits'] as $hit) {
      $source_data = $hit['_source'];
      $response['results'][] = $source_data;
    }

    return $response;
  }
}
