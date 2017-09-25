<?php

namespace Drupal\fsa_es\Plugin\ElasticsearchIndex;

use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\elasticsearch_helper\ElasticsearchLanguageAnalyzer;
use Drupal\elasticsearch_helper\Plugin\ElasticsearchIndexBase;
use Elasticsearch\Client;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Serializer\Serializer;

/**
 * @ElasticsearchIndex(
 *   id = "fsa_ratings_index",
 *   label = @Translation("FSA Ratings Index"),
 *   indexName = "ratings-{langcode}",
 *   entityType = "fsa_establishment",
 *   typeName = "establishment",
 * )
 */
class FsaRatingsIndex extends ElasticsearchIndexBase {

  /**
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $language_manager;

  /**
   * MultilingualContentIndex constructor.
   * @param array $configuration
   * @param string $plugin_id
   * @param mixed $plugin_definition
   * @param \Elasticsearch\Client $client
   * @param \Symfony\Component\Serializer\Serializer $serializer
   * @param \Psr\Log\LoggerInterface $logger
   * @param \Drupal\Core\Language\LanguageManagerInterface $languageManager
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, Client $client, Serializer $serializer, LoggerInterface $logger, LanguageManagerInterface $languageManager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $client, $serializer, $logger);

    $this->language_manager = $languageManager;
  }

  /**
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   * @param array $configuration
   * @param string $plugin_id
   * @param mixed $plugin_definition
   * @return static
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('elasticsearch_helper.elasticsearch_client'),
      $container->get('serializer'),
      $container->get('logger.factory')->get('elasticsearch_helper'),
      $container->get('language_manager')
    );
  }

  /**
   * @inheritdoc
   */
  public function serialize($source, $context = []) {
    /** @var \Drupal\node\Entity\Node $source */

    $data = parent::serialize($source, $context);

    // Add the language code to be used as a token.
    $data['langcode'] = $source->language()->getId();

    return $data;
  }

  /**
   * @inheritdoc
   */
  public function index($source) {
    /** @var \Drupal\node\Entity\Node $source */
    foreach ($source->getTranslationLanguages() as $langcode => $language) {
      if ($source->hasTranslation($langcode)) {
        parent::index($source->getTranslation($langcode));
      }
    }
  }

  /**
   * @inheritdoc
   */
  public function delete($source) {
    /** @var \Drupal\node\Entity\Node $source */
    foreach ($source->getTranslationLanguages() as $langcode => $language) {
      if ($source->hasTranslation($langcode)) {
        parent::delete($source->getTranslation($langcode));
      }
    }
  }

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

        $text_analyzer = ElasticsearchLanguageAnalyzer::get($langcode);
        if ($text_analyzer == 'standard') {
          // Use English analyzer for languages with no specific analyzer found.
          $text_analyzer = 'english';
        }

        $mappingEstablishment = [
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
                'analyzer' => $text_analyzer,
              ],
            ],
          ],
        ];

        $this->client->indices()->putMapping($mappingEstablishment);
      }
    }
  }

  protected function getFiltersAndAnalyzers($langcode) {
    // Get full language name.
    $language = $this->getLanguageName($langcode);
    // Get filters.
    $filters = $this->getFilters($langcode);
    // Analyzer filters.
    $partial_match_filters = [
      'standard',
      'lowercase',
      isset($filters['synonym']) ? 'synonym' : NULL,
      /*
      'asciifolding',
      $language . '_stop',
      $language . '_stemmer',
      */
    ];
    return [
      'analysis' => [
        'tokenizer' => [
          'edge_ngram' => [
            'type' => 'edge_ngram',
            'min_gram' => 4,
            'max_gram' => 10,
            'token_chars' => [
              'letter',
            ],
          ],
        ],
        'filter' => [
          ] + $filters,
        'analyzer' => [
            'keyword_lower' => [
              'type' => 'custom',
              'tokenizer' => 'keyword',
              'filter' => [
                'standard',
                'lowercase',
              ],
            ],
            'partial_match' => [
              'type' => 'custom',
              'tokenizer' => 'standard',
              'char_filter' => [
                'html_strip',
              ],
              'filter' => array_filter($partial_match_filters),
            ],
            'synonym' => [
              'tokenizer' => 'whitespace',
              'filter' => ['synonym'],
            ],
          ] + $this->getLanguageAnalyzer($langcode),
      ],
    ];
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
    $filters = [];
    // Get full language name.
    $language = $this->getLanguageName($langcode);
    // Add synonyms filter.
    if ($synonyms = self::getSynonyms($langcode)) {
      $filters['synonym'] = [
        'type' => 'synonym',
        'synonyms' => $synonyms,
        'tokenizer' => 'standard',
        'ignore_case' => TRUE,
      ];
    }
    /*
    $filters[$language . '_stop'] = [
      'type' => 'stop',
      'stopwords' => sprintf('_%s_', $language),
    ];
    $filters[$language . '_stemmer'] = [
      'type' => 'stemmer',
      'language' => $language,
    ];
    */
    return $filters;
  }

  /**
   * Returns language analyzer.
   *
   * @param $langcode
   *
   * @return array
   *
   * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/analysis-lang-analyzer.html
   */
  protected function getLanguageAnalyzer($langcode) {
    $language = $this->getLanguageName($langcode);
    return [
      $language => [
        'tokenizer' => 'standard',
        'filter' => [
          'lowercase',
          'synonym'
          /*
          $language . '_stop',
          $language . '_stemmer',
          */
        ],
      ],
    ];
  }

  /**
   * Get the name of the language analyzer to be used for a given language code.
   *
   * @param $langcode
   * @return mixed|string
   */
  protected function getLanguageName($langcode) {
    $language_analyzers = [
      'en' => 'english',
      'cy' => 'welsh',
    ];
    if (isset($language_analyzers[$langcode])) {
      return $language_analyzers[$langcode];
    }
    else {
      return 'standard';
    }
  }

  /**
   * Returns synonyms.
   *
   * @param $langcode
   *
   * @return string
   *
   * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/analysis-synonym-tokenfilter.html#_solr_synonyms
   */
  protected static function getSynonyms($langcode) {
    $synonym_file = drupal_get_path('module', 'fsa_es') . '/src/Plugin/ElasticsearchIndex/synonyms.txt';
    return file($synonym_file, FILE_IGNORE_NEW_LINES);
  }
}
