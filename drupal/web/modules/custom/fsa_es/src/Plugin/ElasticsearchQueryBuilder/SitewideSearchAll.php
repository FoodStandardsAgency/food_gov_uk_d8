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

  /**
   * @var \Drupal\fsa_es\SearchService
   */
  protected $ratingsSearchService;

  /**
   * SitewideSearchAll constructor.
   *
   * @param array $configuration
   *   Configuration data.
   * @param string $plugin_id
   *   Search plugin ID.
   * @param mixed $plugin_definition
   *   Plugin definition data.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   Language manager object.
   * @param \Elasticsearch\Client $elasticsearch_client
   *   ES client.
   * @param \Drupal\fsa_es\SearchService $ratings_search_service
   *   Ratings search object.
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
   *   Elasticsearch base query.
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
      // Fuzzy search for All tab.
      $query_must_filters[] = [
        'multi_match' => [
          'query' => $values['keyword'],
          'fields' => ['name^3', 'body'],
          'fuzziness' => 1,
          'operator' => 'and',
        ],
      ];
      // Sort the result by priority list and date created params.content_type.get(doc[\'_type\'].value)
      $query['body']['sort'] = [
        // If the index `type` is `page`, then sort by page taxonomy `content_type`,
        // Else, sort by index `type`.
        '_script' => [
          'type' => 'number',
          'script' => [
            'lang' => 'painless',
            'inline' => '
                if (doc._type.value == "page") {
                  params.page_taxonomy.get(params._source.content_type[0].label)
                } else {
                  params.content_type.get(doc._type.value)
                }
            ',
            'params' => [
              'content_type' => [
                'page' => 0,
                'news' => 10,
                'alert' => 20,
                'consultation' => 30,
                'research' => 40,
              ],
              'page_taxonomy' => [
                'Business guidance' => 1,
                'Consumer guidance' => 2,
                'About us' => 3,
                'Help' => 4,
                'News & alerts' => 5,
                'Other' => 6,
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
   *   Array of indices that search should be performed on.
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
