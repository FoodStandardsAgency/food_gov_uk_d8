<?php

namespace Drupal\fsa_es\Plugin\ElasticsearchIndex;

/**
 * @ElasticsearchIndex(
 *   id = "alert_index",
 *   label = @Translation("FSA Alert Index"),
 *   indexName = "alert",
 *   typeName = "alert",
 *   entityType = "node",
 *   bundle = "alert",
 * )
 */
class FsaAlertIndex extends FsaIndexBase {

  /**
   * {@inheritdoc}
   */
  public function setup() {
    // Index name is not language specific.
    $index_name = $this->pluginDefinition['indexName'];

    // Analyzers and filters should be in English.
    $langcode = 'en';

    if (!$this->client->indices()->exists(['index' => $index_name])) {
      $this->client->indices()->create([
        'index' => $index_name,
        'body' => [
          'number_of_shards' => 1,
          'number_of_replicas' => 0,
        ] + $this->getFiltersAndAnalyzers($langcode),
      ]);

      // Get language name which is also a text analyzer name.
      $text_analyzer = $this->getLanguageName($langcode);

      $mapping = [
        'index' => $index_name,
        'type' => 'alert',
        'body' => [
          'properties' => [
            'id' => [
              'type' => 'integer',
            ],
            'entity_type' => [
              'type' => 'keyword',
            ],
            'langcode' => [
              'type' => 'keyword',
            ],
            // Refers to news type that is displayed as a facet on the news
            // search page.
            'news_type' => [
              'type' => 'keyword',
            ],
            'name' => [
              'type' => 'text',
              'analyzer' => $text_analyzer,
            ],
            // Intro is included in the body cause there's no need to
            // separate them.
            'body' => [
              'type' => 'text',
              'analyzer' => $text_analyzer,
            ],
            'nation' => [
              'properties' => [
                'id' => ['type' => 'keyword'],
                'label' => [
                  'type' => 'text',
                  'index' => false,
                  'fields' => [
                    'keyword' => [
                      'type' => 'keyword',
                    ],
                  ],
                ],
              ],
            ],
            'created' => [
              'type' => 'date',
            ],
            'updated' => [
              'type' => 'date',
            ],
          ],
        ],
      ];

      $this->client->indices()->putMapping($mapping);
    }
  }

}
