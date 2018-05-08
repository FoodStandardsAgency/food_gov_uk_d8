<?php

namespace Drupal\fsa_es\Plugin\ElasticsearchQueryBuilder;

use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\fsa_es\SearchService;
use Elasticsearch\Client;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @ElasticsearchQueryBuilder(
 *   id = "sitewide_search_all",
 *   label = @Translation("Global search: All"),
 *   description = @Translation("Provides query builder for site-wide global search.")
 * )
 */
class SitewideSearchAll extends SitewideSearchBase {

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
   * Builds Elasticsearch base query.
   *
   * @return array
   */
  public function buildBaseQuery() {
    // Get filter values.
    $values = $this->getFilterValues();

    $query_must_filters = [];
    $query_filter_filters = [];

    $query = [
      'index' => $this->getIndices(),
      'body' => [],
    ];

    // Apply the filters to the query.
    if (!empty($values['keyword'])) {
      // Fuzzy search for All tab
      $query_must_filters[] = [
        'multi_match' => [
          'query' => $values['keyword'],
          'fields' => ['name^3', 'body'],
          'fuzziness' => 1,
          'operator' => 'and',
        ],
      ];
      // Sort the result by priority list and date created
      $query['body']['sort'] = [
        '_script' => [
          'type' => 'number',
          'script' => [
            'source' => "params.factor.get(doc[\"_type\"].value)",
            'params' => [
              'factor' => [
                'page' => 0,
                'news' => 1,
                'alert' => 2,
                'consultation' => 3,
                'research' => 4,
              ],
            ],
          ],
          'order' => 'asc',
        ],
        'created' => 'desc',
      ];
    }
    else {
      // Sort by created if no keywords are given.
      $query['body']['sort'] = ['created' => 'desc'];
    }

    // Assign the text search filters to the query in the 'must' section.
    foreach ($query_must_filters as $filter) {
      $query['body']['query']['bool']['must'][] = $filter;
    }

    // Assign the text search filters to the query in the 'filter' section.
    foreach ($query_filter_filters as $filter) {
      $query['body']['query']['bool']['filter'][] = $filter;
    }

    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function buildQuery() {
    // Get the base query.
    $query = $this->buildBaseQuery();

    return $query;
  }

  /**
   * Returns a list of indices that search should be performed on.
   *
   * @return array
   */
  protected function getIndices() {
    $langcode = $this->currentLanguage->getId();

    return [
      'page-' . $langcode,
      'news-' . $langcode,
      'alert',
      'consultation-' . $langcode,
      'research-' . $langcode,
    ];
  }
}
