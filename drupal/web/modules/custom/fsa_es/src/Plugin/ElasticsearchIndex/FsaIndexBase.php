<?php

namespace Drupal\fsa_es\Plugin\ElasticsearchIndex;

use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\elasticsearch_helper\Plugin\ElasticsearchIndexBase;
use Elasticsearch\Client;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Serializer\Serializer;

/**
 * Class FsaIndexBase
 */
class FsaIndexBase extends ElasticsearchIndexBase {

  /**
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $language_manager;

  /**
   * PageIndex constructor.
   *
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
        $translation = $source->getTranslation($langcode);

        $exclude = 0;
        if ($translation->hasField('field_search_exclude')) {
          $exclude = $translation->get('field_search_exclude')->getValue()[0]['value'];
        }

        if ($translation->isPublished() && $exclude != 0) {
          parent::index($translation);
        }
        else {
          parent::delete($translation);
        }
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

  /**
   * Get the name of the language analyzer to be used for a given language code.
   *
   * @param $langcode
   *
   * @return mixed|string
   */
  protected function getLanguageName($langcode) {
    $language_analyzers = [
      'en' => 'english',
      // There's no language analyzer for Welsh implemented in ES.
      // 'cy' => 'welsh',
    ];

    if (isset($language_analyzers[$langcode])) {
      return $language_analyzers[$langcode];
    }

    // Use english as a default language since in our case there's a lot of
    // English text in the 'cy' (Welsh) index.
    return 'english';
  }

  /**
   * Returns filters and analyzers.
   */
  protected function getFiltersAndAnalyzers($langcode) {
    // Get filters.
    $filters = $this->getFilters($langcode);

    return [
      'analysis' => [
        'filter' => $filters,
        'analyzer' => $this->getLanguageAnalyzer($langcode),
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

    $filters[$language . '_stop'] = [
      'type' => 'stop',
      'stopwords' => sprintf('_%s_', $language),
    ];
    $filters[$language . '_stemmer'] = [
      'type' => 'stemmer',
      'language' => $language,
    ];
    $filters[$language . '_possessive_stemmer'] = [
      'type' => 'stemmer',
      'language' => 'possessive_' . $language,
    ];

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
          // Synonyms filter goes first to add tokens.
          'synonym',
          // Lowercase filter should go before stemmers to normalize the input
          // data. Otherwise strings like "Ivy" and "ivy" will be stemmed
          // differently.
          'lowercase',
          // Possessive stemmer should go next in the list; if it goes after
          // generic stemmer, apostrophes will remain at the end of the tokens.
          // To test this out, try this:
          // curl 'http://localhost:9200/ratings-en/_analyze?pretty=true' -d '{
          //     "field": "name",
          //     "text" : "Santa'\''s will bring all the joy"
          // }'
          $language . '_possessive_stemmer',
          $language . '_stemmer',
          // Stopword filter goes last to remove tokens.
          $language . '_stop',
        ],
      ],
    ];
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
