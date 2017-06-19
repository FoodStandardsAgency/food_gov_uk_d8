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

    // Define the base query
    $base_query = $query = [
      'index' => ['ratings'],
      'size' => 1000,
      'body' => [
        'query' => [
          'bool' => [
            'must' => [
              ['term' => ['_type' => 'establishment']],
              ['match_phrase' => ['name' => $input]],
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
