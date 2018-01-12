<?php

namespace Drupal\fsa_es\Plugin\ElasticsearchIndex;

/**
 * @ElasticsearchIndex(
 *   id = "alert_index",
 *   label = @Translation("FSA Alert Index"),
 *   indexName = "alert-{langcode}",
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
    // Create one index per language, so that we can have different analyzers.
    foreach ($this->language_manager->getLanguages() as $langcode => $language) {
      $index_name = 'alert-' . $langcode;

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
              'langcode' => [
                'type' => 'keyword',
              ],
              // Refers to news type that is displayed as a facet on the news
              // search page:
              // - "news" for "news" content type
              // - value of "field_alert_type" field on "alert" content type
              //   (allergy or food alert)
              // - value of "field_consultations_type" field on "consultation"
              //   content type
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
