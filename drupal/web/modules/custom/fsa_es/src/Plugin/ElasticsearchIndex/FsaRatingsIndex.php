<?php

namespace Drupal\fsa_es\Plugin\ElasticsearchIndex;

use Drupal\elasticsearch_helper\Plugin\ElasticsearchIndexBase;

/**
 * @ElasticsearchIndex(
 *   id = "fsa_ratings_index",
 *   label = @Translation("FSA Ratings Index"),
 *   indexName = "ratings",
 *   entityType = "fsa_establishment",
 *   typeName = "establishment",
 * )
 */
class FsaRatingsIndex extends ElasticsearchIndexBase {

  private static $mappingEstablishment = [
    'index' => 'ratings',
    'type' => 'establishment',
    'body' => [
      'properties' => [
        'id' => [
          'type' => 'integer',
        ],
        'name' => [
          'type' => 'text',
          'fields' => [
            'keyword' => [
              'type' => 'keyword',
            ],
          ],
          'analyzer' => 'english',
        ],
        'address' => [
          'type' => 'text',
          'index' => 'not_analyzed',
        ],
        'businesstype' => [
          'properties' => [
            'id' => ['type' => 'integer'],
            'label' => [
              'type' => 'text',
              'index' => 'not_analyzed',
              'fields' => [
                'keyword' => [
                  'type' => 'keyword',
                ],
              ],
            ],
          ],
        ],
        'geolocation' => [
          'type' => 'geo_point',
        ],
        'localauthoritycode' => [
          'properties' => [
            'id' => ['type' => 'integer'],
            'label' => [
              'type' => 'text',
              'index' => 'not_analyzed',
              'fields' => [
                'keyword' => [
                  'type' => 'keyword',
                ],
              ],
            ],
          ],
        ],
        'newratingpending' => [
          'type' => 'boolean',
        ],
        'phone' => [
          'type' => 'text',
          'index' => 'not_analyzed',
        ],
        'postcode' => [
          'type' => 'text',
          'index' => 'not_analyzed',
        ],
        'ratingdate' => [
          'type' => 'date',
        ],
        'ratingvalue' => [
          'type' => 'text',
          'index' => 'not_analyzed',
          'fields' => [
            'keyword' => [
              'type' => 'keyword',
            ],
          ],
        ],
        'score_confidence' => [
          'type' => 'integer',
        ],
        'score_hygiene' => [
          'type' => 'integer',
        ],
        'score_structural' => [
          'type' => 'integer',
        ],
        'combinedvalues' => [
          'type' => 'text',
          'analyzer' => 'english',
        ],
      ],
    ],
  ];

  public function setup() {
    if (!$this->client->indices()->exists(['index' => 'ratings'])) {
      $this->client->indices()->create([
        'index' => 'ratings',
        'body' => [
          'number_of_shards' => 1,
          'number_of_replicas' => 0,
        ],
      ]);

      $this->client->indices()->putMapping(self::$mappingEstablishment);
    }
  }

}
