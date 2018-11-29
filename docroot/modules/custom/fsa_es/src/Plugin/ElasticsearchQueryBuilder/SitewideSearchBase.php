<?php

namespace Drupal\fsa_es\Plugin\ElasticsearchQueryBuilder;

use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\elasticsearch_helper_views\Plugin\ElasticsearchQueryBuilder\ElasticsearchQueryBuilderPluginBase;
use Elasticsearch\Client;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class SitewideSearchBase.
 */
abstract class SitewideSearchBase extends ElasticsearchQueryBuilderPluginBase {

  /**
   * @var \Drupal\Core\Language\LanguageInterface
   */
  protected $currentLanguage;

  /**
   * @var \Elasticsearch\Client
   */
  protected $elasticsearchClient;

  /**
   * SitewideSearchBase constructor.
   *
   * @param array $configuration
   *   Configuration.
   * @param string $plugin_id
   *   Plugin ID.
   * @param mixed $plugin_definition
   *   Plugin definition.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   Language Manager.
   * @param \Elasticsearch\Client $elasticsearch_client
   *   ES client.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, LanguageManagerInterface $language_manager, Client $elasticsearch_client) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->currentLanguage = $language_manager->getCurrentLanguage();
    $this->elasticsearchClient = $elasticsearch_client;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('language_manager'),
      $container->get('elasticsearch_helper.elasticsearch_client')
    );
  }

  /**
   * Returns aggregations.
   *
   * Return array.
   */
  public function getAggregations() {
    return [];
  }

  /**
   * Translate aggregates to options.
   *
   * @param array $aggs_buckets
   *   Aggregation buckets.
   *
   * @return array
   *   Array of option values.
   */
  public function aggsToOptions(array $aggs_buckets = []) {
    $options = [];

    foreach ($aggs_buckets as $aggs_bucket) {
      $value = $aggs_bucket['key'];
      $options[$aggs_bucket['key']] = (string) $value;
    }

    return $options;
  }

  /**
   * Private helper function to sort array by another array.
   *
   * 1:1 copy of Drupal\fsa_es\SearchService::defineAndSortArrayItems().
   *
   * @param array $array
   *   The array to define..
   * @param array $definingArray
   *   The array with keys defining sort and items to keep.
   *
   * @return array
   *   Sorted array.
   */
  public static function defineAndSortArrayItems(array $array, array $definingArray) {
    $modified_array = [];
    foreach ($definingArray as $key) {
      if (array_key_exists($key, $array)) {
        $modified_array[$key] = $array[$key];
        unset($array[$key]);
      }
    }
    return $modified_array;
  }

  /**
   * Returns a list of consultation statuses.
   *
   * @return array
   *   Array of consultation status values.
   */
  public function getConsultationStatusFilterOptions() {
    $aggregations = $this->getAggregations();

    // Define human readable values.
    $human_readable_values = [
      1 => $this->t('Open'),
      0 => $this->t('Closed'),
    ];

    // Get aggregated values.
    $agg_values = array_column($aggregations['consultation_status'], 'key');

    // Return aggregated values with human readable values.
    return array_intersect_key($human_readable_values, array_combine($agg_values, $agg_values));
  }

  /**
   * Returns a list of nations.
   *
   * @return array
   *   Array of nation values.
   */
  public function getNationFilterOptions() {
    $aggregations = $this->getAggregations();

    return $this->aggsToOptions($aggregations['nation']);
  }

  /**
   * Returns a filter for bool if responses are published.
   *
   * @return array
   *   Array of published responses.
   */
  public function getConsultationResponsesPublishedFilterOptions() {
    return [
      1 => $this->t('Responses published'),
    ];
  }

  /**
   * Returns a filter for year.
   *
   * @return array
   *   Array of years.
   */
  public function getConsultationYearFilterOptions() {
    $aggregations = $this->getAggregations();

    // Get aggregated values.
    $start_date_values = array_column($aggregations['consultation_start_date'], 'key_as_string');
    $close_date_values = array_column($aggregations['consultation_close_date'], 'key_as_string');
    $merged_values = array_unique(array_merge($start_date_values, $close_date_values));
    sort($merged_values);

    return array_combine($merged_values, $merged_values);

  }

}
