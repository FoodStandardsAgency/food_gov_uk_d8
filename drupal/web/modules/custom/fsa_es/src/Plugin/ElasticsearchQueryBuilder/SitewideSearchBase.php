<?php

namespace Drupal\fsa_es\Plugin\ElasticsearchQueryBuilder;

use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\elasticsearch_helper_views\Plugin\ElasticsearchQueryBuilder\ElasticsearchQueryBuilderPluginBase;
use Elasticsearch\Client;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class SitewideSearchBase
 */
abstract class SitewideSearchBase extends ElasticsearchQueryBuilderPluginBase {

  /** @var \Drupal\Core\Language\LanguageInterface $currentLanguage */
  protected $currentLanguage;

  /** @var \Elasticsearch\Client $elasticsearchClient */
  protected $elasticsearchClient;

  /** @var null|\Symfony\Component\HttpFoundation\Request $request */
  protected $request;

  /**
   * SitewideSearchBase constructor.
   *
   * @param array $configuration
   * @param string $plugin_id
   * @param mixed $plugin_definition
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   * @param \Elasticsearch\Client $elasticsearch_client
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, LanguageManagerInterface $language_manager, Client $elasticsearch_client, RequestStack $request_stack) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->currentLanguage = $language_manager->getCurrentLanguage();
    $this->elasticsearchClient = $elasticsearch_client;
    $this->request = $request_stack->getCurrentRequest();
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
      $container->get('elasticsearch_helper.elasticsearch_client'),
      $container->get('request_stack')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFilterValues() {
    $values = [];

    // Get query parameters.
    $params = $this->request->query->all();

    if (!empty($this->view->filter)) {
      /** @var \Drupal\views\Plugin\views\filter\FilterPluginBase $filter */
      foreach ($this->view->filter as $filter) {
        $info = $filter->exposedInfo();

        // A special case for "q" query parameter which is unique in Drupal
        // and which is not automatically populated by Views.
        if (isset($info['value']) && $info['value'] == 'q' && isset($params['q'])) {
          $values[$filter->realField] = $params['q'];
        }
        else {
          $values[$filter->realField] = $filter->value;
        }
      }
    }

    return $values;
  }

  /**
   * Translate aggregates to options.
   *
   * @param array $aggs_buckets
   *
   * @return array
   */
  public function aggsToOptions($aggs_buckets = []) {
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

}
