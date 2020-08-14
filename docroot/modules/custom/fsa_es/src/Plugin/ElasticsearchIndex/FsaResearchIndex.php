<?php

namespace Drupal\fsa_es\Plugin\ElasticsearchIndex;

/**
 * @ElasticsearchIndex(
 *   id = "research_index",
 *   label = @Translation("FSA Research Index"),
 *   indexName = "research-{langcode}",
 *   typeName = "research",
 *   entityType = "node",
 *   bundle = "research_project",
 * )
 */
class FsaResearchIndex extends FsaIndexBase {

  /**
   * {@inheritdoc}
   */
  public function setup() {
    // Create one index per language, so that we can have different analyzers.
    foreach ($this->languageManager->getLanguages() as $langcode => $language) {
      $index_name = 'research-' . $langcode;

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
          'type' => 'news',
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
              'name' => [
                'type' => 'text',
                'analyzer' => $text_analyzer,
              ],
              'project_code' => [
                'type' => 'keyword',
              ],
              'intro' => [
                'type' => 'text',
                'analyzer' => $text_analyzer,
              ],
              'body' => [
                'type' => 'text',
                'analyzer' => $text_analyzer,
              ],
              'topics' => [
                'properties' => [
                  'id' => ['type' => 'keyword'],
                  'label' => [
                    'type' => 'text',
                    'index' => FALSE,
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
                    'index' => FALSE,
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

}
