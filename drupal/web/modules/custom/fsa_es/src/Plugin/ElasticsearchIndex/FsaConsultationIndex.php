<?php

namespace Drupal\fsa_es\Plugin\ElasticsearchIndex;

/**
 * @ElasticsearchIndex(
 *   id = "consultation_index",
 *   label = @Translation("FSA Consultation Index"),
 *   indexName = "consultation-{langcode}",
 *   typeName = "consultation",
 *   entityType = "node",
 *   bundle = "consultation",
 * )
 */
class FsaConsultationIndex extends FsaIndexBase {

  /**
   * {@inheritdoc}
   */
  public function setup() {
    // Create one index per language, so that we can have different analyzers.
    foreach ($this->language_manager->getLanguages() as $langcode => $language) {
      $index_name = $this->getIndexName(['langcode' => $langcode]);

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
              // Refers to news type that is displayed as a facet on the news
              // search page.
              'news_type' => [
                'type' => 'keyword'
              ],
              'status' => [
                'type' => 'boolean'
              ],
              'responses_published' => [
                'type' => 'boolean'
              ],
              'consultation_start_date' => [
                'type' => 'date',
              ],
              'consultation_close_date' => [
                'type' => 'date',
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
