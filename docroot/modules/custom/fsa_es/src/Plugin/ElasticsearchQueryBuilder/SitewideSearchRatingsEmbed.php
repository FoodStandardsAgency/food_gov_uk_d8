<?php

namespace Drupal\fsa_es\Plugin\ElasticsearchQueryBuilder;

use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\fsa_es\SearchService;
use Elasticsearch\Client;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @ElasticsearchQueryBuilder(
 *   id = "sitewide_search_ratings_embed",
 *   label = @Translation("Global search: Ratings (embedded)"),
 *   description = @Translation("Provides query builder for embedded ratings search.")
 * )
 */
class SitewideSearchRatingsEmbed extends SitewideSearchBase {

  /**
   * @var \Drupal\fsa_es\SearchService
   */
  protected $ratingsSearchService;

  /**
   * SitewideSearchRatingsEmbed constructor.
   *
   * @param array $configuration
   *   Configuration.
   * @param string $plugin_id
   *   Plugin id value.
   * @param mixed $plugin_definition
   *   Plugin definition value.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   Language manager.
   * @param \Elasticsearch\Client $elasticsearch_client
   *   ES client.
   * @param \Drupal\fsa_es\SearchService $ratings_search_service
   *   Ratings search service client.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, LanguageManagerInterface $language_manager, Client $elasticsearch_client, SearchService $ratings_search_service) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $language_manager, $elasticsearch_client);

    $this->ratingsSearchService = $ratings_search_service;
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
      $container->get('fsa_es.search_service')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildQuery() {
    // Get query plugin.
    $query_plugin = $this->view->getQuery();

    // Get arguments.
    $arguments = $this->getArgumentValues();

    // Get keyword.
    $keyword = $arguments['keyword'];

    // Get query.
    $query = $this->ratingsSearchService->buildQuery($this->currentLanguage, $keyword, [], $query_plugin->getLimit(), $query_plugin->offset);

    return $query;
  }

}
