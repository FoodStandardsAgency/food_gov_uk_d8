<?php

namespace Drupal\fsa_es\Plugin\ElasticsearchQueryBuilder;

use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\fsa_es\SearchService;
use Drupal\views\ViewExecutable;
use Elasticsearch\Client;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @ElasticsearchQueryBuilder(
 *   id = "sitewide_search_ratings_embed",
 *   label = @Translation("Ratings (embedded)"),
 *   description = @Translation("Provides query builder for embedded ratings search.")
 * )
 */
class SitewideSearchRatingsEmbed extends SitewideSearchBase {

  /** @var \Drupal\fsa_es\SearchService $ratingsSearchService */
  protected $ratingsSearchService;

  /**
   * {@inheritdoc}
   *
   * @param \Drupal\fsa_es\SearchService $ratings_search_service
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
  public function buildQuery(ViewExecutable $view) {
    // Get query plugin.
    $query_plugin = $view->getQuery();

    // Get arguments.
    $arguments = $this->getArgumentValues($view);

    // Get keyword.
    $keyword = $arguments['keyword'];

    // Get query.
    $query = $this->ratingsSearchService->buildQuery($this->currentLanguage, $keyword, [], $query_plugin->getLimit(), $query_plugin->offset);

    return $query;
  }

}
