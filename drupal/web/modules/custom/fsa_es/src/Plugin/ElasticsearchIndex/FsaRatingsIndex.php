<?php

namespace Drupal\fsa_es\Plugin\ElasticsearchIndex;

/**
 * @ElasticsearchIndex(
 *   id = "fsa_ratings_index",
 *   label = @Translation("FSA Ratings Index"),
 *   indexName = "ratings-{langcode}",
 *   entityType = "fsa_establishment",
 *   typeName = "establishment",
 * )
 */
class FsaRatingsIndex extends FsaIndexBase {

  /**
   * {@inheritdoc}
   */
  public function setup() {
    // Create one index per language, so that we can have different analyzers.
    foreach ($this->language_manager->getLanguages() as $langcode => $language) {
      if (!$this->client->indices()->exists(['index' => 'ratings-' . $langcode])) {
        $this->client->indices()->create([
          'index' => 'ratings-' . $langcode,
          'body' => [
            'number_of_shards' => 1,
            'number_of_replicas' => 0,
          ] + $this->getFiltersAndAnalyzers($langcode),
        ]);

        // Get language name which is also a text analyzer name.
        $text_analyzer = $this->getLanguageName($langcode);

        $mapping = [
          'index' => 'ratings-' . $langcode,
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
                'analyzer' => $text_analyzer,
              ],
              'address' => [
                'type' => 'text',
                'analyzer' => $text_analyzer,
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
              'postcode_tokenized' => [
                'type' => 'text',
                'analyzer' => $text_analyzer,
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
              'fhrs_ratingvalue' => [
                'type' => 'text',
                'index' => 'not_analyzed',
                'fields' => [
                  'keyword' => [
                    'type' => 'keyword',
                  ],
                ],
              ],
              'fhis_ratingvalue' => [
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
                'analyzer' => $text_analyzer,
              ],
              'combined_name_postcode' => [
                'type' => 'text',
                'analyzer' => $text_analyzer,
              ],
              'combined_name_location' => [
                'type' => 'text',
                'analyzer' => $text_analyzer,
              ],
              'combined_location_postcode' => [
                'type' => 'text',
                'analyzer' => $text_analyzer,
              ],
            ],
          ],
        ];

        $this->client->indices()->putMapping($mapping);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function getFiltersAndAnalyzers($langcode) {
    $filters_analyzers = parent::getFiltersAndAnalyzers($langcode);

    // This characted filter replaces spaces with an empty string.
    $filters_analyzers['analysis']['char_filter']['concat'] = [
      'type' => 'pattern_replace',
      'pattern' => '\u0020',
      'replacement' => '',
    ];

    // This analyzer edge n-grams the postcode - removes the spaces,
    // lower-cases, makes at least 2 char long tokens from the left side.
    // E.g., SW19 5EG => "sw", "sw1", "sw19", "sw195", "sw195e", "sw195eg"
    $filters_analyzers['analysis']['analyzer']['postcode_edge_ngram'] = [
      'type' => 'custom',
      'tokenizer' => 'standard',
      'char_filter' => [
        'concat',
      ],
      'filter' => [
        'standard',
        'lowercase',
        'postcode_edge_ngram'
      ],
    ];

    return $filters_analyzers;
  }

  /**
   * Returns filters.
   *
   * @param $langcode
   *
   * @return array
   *
   * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/analysis-lang-analyzer.html
   */
  protected function getFilters($langcode) {
    $filters = parent::getFilters($langcode);

    $filters['postcode_edge_ngram'] = [
      'type' => 'edge_ngram',
      'min_gram' => 2,
      'max_gram' => 8,
    ];

    return $filters;
  }

}
