<?php

namespace Drupal\fsa_es\Plugin\ElasticsearchQueryBuilder;

use Drupal\fsa_es\SearchService;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @ElasticsearchQueryBuilder(
 *   id = "sitewide_search_ratings_embed",
 *   label = @Translation("Global search: Ratings (embedded)"),
 *   description = @Translation("Provides query builder for embedded ratings search.")
 * )
 */
class SitewideSearchRatingsEmbed extends SitewideSearchBase {

  /** @var \Drupal\fsa_es\SearchService $ratingsSearchService */
  protected $ratingsSearchService;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('language_manager'),
      $container->get('elasticsearch_helper.elasticsearch_client'),
      $container->get('request_stack')
    );

    // Set ratings search service instance.
    $instance->setRatingsSearchService($container->get('fsa_es.search_service'));

    return $instance;
  }

  /**
   * Sets ratings search service instance.
   *
   * @param \Drupal\fsa_es\SearchService $ratings_search_service
   */
  public function setRatingsSearchService(SearchService $ratings_search_service) {
    $this->ratingsSearchService = $ratings_search_service;
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
