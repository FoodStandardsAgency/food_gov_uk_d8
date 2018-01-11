<?php

namespace Drupal\fsa_es\Plugin\ElasticsearchIndex;

/**
 * @ElasticsearchIndex(
 *   id = "page_index",
 *   label = @Translation("FSA Page Index"),
 *   indexName = "page-{langcode}",
 *   entityType = "node",
 *   bundle = "page",
 *   typeName = "page",
 * )
 */
class FsaPageIndex extends FsaIndexBase {

  /**
   * {@inheritdoc}
   */
  public function setup() {
    // Create one index per language, so that we can have different analyzers.
    foreach ($this->language_manager->getLanguages() as $langcode => $language) {
      if (!$this->client->indices()->exists(['index' => 'page-' . $langcode])) {
        $this->client->indices()->create([
          'index' => 'page-' . $langcode,
          'body' => [
            'number_of_shards' => 1,
            'number_of_replicas' => 0,
          ] + $this->getFiltersAndAnalyzers($langcode),
        ]);

        // Get language name which is also a text analyzer name.
        $text_analyzer = $this->getLanguageName($langcode);

        $mapping = [
          'index' => 'page-' . $langcode,
          'type' => 'page',
          'body' => [
            'properties' => [
              'id' => [
                'type' => 'integer',
              ],
              'langcode' => [
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
              'content_type' => [
                'properties' => [
                  'id' => ['type' => 'keyword'],
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
              'audience' => [
                'properties' => [
                  'id' => ['type' => 'keyword'],
                  'depth' => ['type' => 'integer'],
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
              'nation' => [
                'properties' => [
                  'id' => ['type' => 'keyword'],
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

}
