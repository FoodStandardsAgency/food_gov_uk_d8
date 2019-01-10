<?php

namespace Drupal\fsa_es;

use Drupal\Component\Utility\Html;
use Drupal\Core\Language\LanguageInterface;
use Elasticsearch\Client;

/**
 * Class SearchService.
 *
 * @package Drupal\fsa_es
 */
class SearchService {

  const DEFAULT_MAX_RESULT_ITEMS = 100;
  const SEARCHABLE_FIELDS = [
    'name^3',
    'localauthoritycode.label.keyword^5',
    'address',
    'postcode_tokenized',
  ];

  /**
   * @var \Elasticsearch\Client*/
  private $client;

  /**
   * Constructs a new SearchService object.
   */
  public function __construct(Client $client) {
    $this->client = $client;
  }

  /**
   * Builds Elasticsearch base query.
   *
   * @param \Drupal\Core\Language\LanguageInterface $language
   *   Language interface.
   * @param array $filters
   *   Query filters.
   * @param int $max_items
   *   Max items.
   * @param int $offset
   *   Offset value.
   *
   * @return array
   *   Array of results.
   */
  public function buildBaseQuery(LanguageInterface $language, array $filters, $max_items = self::DEFAULT_MAX_RESULT_ITEMS, $offset = 0) {
    $query_filter_filters = [];

    $query = [
      // Each language has a separate index.
      'index' => ['ratings-' . $language->getId()],
      'size' => $max_items,
      'from' => $offset,
      'body' => [
        'query' => [
          'bool' => [
            'must' => [],
          ],
        ],
        // Aggregations needed for the potential facet filters.
        'aggs' => [
          'business_types' => [
            'terms' => [
              'field' => 'businesstype.label.keyword',
            ],
          ],
          'local_authorities' => [
            'terms' => [
              'field' => 'localauthoritycode.label.keyword',
            ],
          ],
          'rating_values' => [
            'terms' => [
              'field' => 'ratingvalue.keyword',
            ],
          ],
          'fhis_rating_values' => [
            'terms' => [
              'field' => 'fhis_ratingvalue.keyword',
            ],
          ],
          'fhrs_rating_values' => [
            'terms' => [
              'field' => 'fhrs_ratingvalue.keyword',
            ],
          ],
        ],
      ],
    ];

    // Get sorting param from url.
    $sort = Html::escape(\Drupal::request()->query->get('sort'));

    // And pass sorting for ES.
    switch ($sort) {
      case 'ratings_asc':
        $query['body']['sort'] = ['ratingvalue.keyword' => 'asc'];
        break;

      case 'ratings_desc':
        $query['body']['sort'] = ['ratingvalue.keyword' => 'desc'];
        break;

      case 'name_asc':
        $query['body']['sort'] = ['name.keyword' => 'asc'];
        break;

      case 'name_desc':
        $query['body']['sort'] = ['name.keyword' => 'desc'];
        break;
    }

    // Apply the filters to the query:
    if (!empty($filters['business_type'])) {
      $ids = explode(',', $filters['business_type']);
      $query_filter_filters[] = ['terms' => ['businesstype.label.keyword' => $ids]];
    }
    if (!empty($filters['local_authority'])) {
      $ids = explode(',', $filters['local_authority']);
      $query_filter_filters[] = ['terms' => ['localauthoritycode.label.keyword' => $ids]];
    }
    if (isset($filters['rating_value'])) {
      $ids = explode(',', $filters['rating_value']);
      $query_filter_filters[] = ['terms' => ['ratingvalue.keyword' => $ids]];
    }
    if (isset($filters['fhis_rating_value'])) {
      $ids = explode(',', $filters['fhis_rating_value']);
      $query_filter_filters[] = ['terms' => ['fhis_ratingvalue.keyword' => $ids]];
    }
    if (isset($filters['fhrs_rating_value'])) {
      $ids = explode(',', $filters['fhrs_rating_value']);
      $query_filter_filters[] = ['terms' => ['fhrs_ratingvalue.keyword' => $ids]];
    }

    // Assign the term filters to the query in the 'filter' section.
    $query['body']['query']['bool']['filter'] = $query_filter_filters;

    return $query;
  }

  /**
   * Builds Elasticsearch query.
   *
   * @param \Drupal\Core\Language\LanguageInterface $language
   *   Language interface.
   * @param string $input
   *   Search keywords.
   * @param array $filters
   *   Filter options.
   * @param int $max_items
   *   Max items value.
   * @param int $offset
   *   Offset value.
   *
   * @return array
   *   Array of query results.
   */
  public function buildQuery(LanguageInterface $language, $input = '', array $filters = [], $max_items = self::DEFAULT_MAX_RESULT_ITEMS, $offset = 0) {
    $query_must_filters = [];
    $query_should_filters = [];
    $query = $this->buildBaseQuery($language, $filters, $max_items, $offset);

    if (!empty($input)) {
      $query_must_filters[] = [
        'multi_match' => [
          'query' => $input,
          'fields' => self::SEARCHABLE_FIELDS,
          'type' => 'cross_fields',
          'operator' => 'and',
        ],
      ];
      // Add postcode as an additional booster (should clause).
      $query_should_filters[] = [
        'match' => [
          'postcode_tokenized' => [
            'query' => $input,
          ],
        ],
      ];
      $query_should_filters[] = [
        'match_phrase' => [
          'name' => [
            'query' => $input,
            'slop' => 2,
            'boost' => 10,
          ],
        ],
      ];
      $query_should_filters[] = [
        'match_phrase' => [
          'combined_name_location' => [
            'query' => $input,
            'slop' => 1,
            'boost' => 5,
          ],
        ],
      ];
      $query_should_filters[] = [
        'match_phrase' => [
          'combined_name_postcode' => [
            'query' => $input,
            'slop' => 0,
            'boost' => 3,
          ],
        ],
      ];
      $query_should_filters[] = [
        'match_phrase' => [
          'combined_location_postcode' => [
            'query' => $input,
            'slop' => 0,
            'boost' => 1,
          ],
        ],
      ];
    }

    // Assign the term filters to the query in the 'must' section.
    foreach ($query_must_filters as $filter) {
      $query['body']['query']['bool']['must'][] = $filter;
    }

    // Assign the term filters to the query in the 'should' section.
    foreach ($query_should_filters as $filter) {
      $query['body']['query']['bool']['should'][] = $filter;
    }

    return $query;
  }

  /**
   * Builds fallback Elasticsearch query.
   *
   * @param \Drupal\Core\Language\LanguageInterface $language
   *   Language interface.
   * @param string $input
   *   Search keywords.
   * @param array $filters
   *   Filter options.
   * @param int $max_items
   *   Max items value.
   * @param int $offset
   *   Offset value.
   *
   * @return array
   *   Array of query results.
   */
  public function buildFallbackQuery(LanguageInterface $language, $input = '', array $filters = [], $max_items = self::DEFAULT_MAX_RESULT_ITEMS, $offset = 0) {
    $query_must_filters = [];
    $query = $this->buildBaseQuery($language, $filters, $max_items, $offset);

    // Assign looser settings to the multi match and match_phrase queries (with fuzziness)
    $query_must_filters[] = [
      'match' => [
        'combinedvalues' => [
          'query' => $input,
          'fuzziness' => 'AUTO',
          // Don't let the first letter be fuzzy.
          'prefix_length' => 1,
          'operator' => 'and',
        ],
      ],
    ];

    foreach ($query_must_filters as $f) {
      $query['body']['query']['bool']['must'][] = $f;
    }

    return $query;
  }

  /**
   * @param \Drupal\Core\Language\LanguageInterface $language
   *   The preferred language.
   * @param string $input
   *   Search keywords.
   * @param array $filters
   *   Additional filters for the search query.
   * @param int $max_items
   *   Returned max items.
   * @param int $offset
   *   Offset for the results.
   *
   * @return array
   *   An associated array containing results and metadata. Something like this: ['results' => [...], 'total' => 100, 'aggs' => [...]]
   */
  public function search(LanguageInterface $language, $input = '', array $filters = [], $max_items = self::DEFAULT_MAX_RESULT_ITEMS, $offset = 0) {
    // Get query.
    $query = $this->buildQuery($language, $input, $filters, $max_items, $offset);

    // Execute the query.
    $result = $this->client->search($query);

    // If no results found, try fallback query.
    if ($result['hits']['total'] == 0 && !empty($input)) {
      $query = $this->buildFallbackQuery($language, $input, $filters, $max_items, $offset);

      // Re-run the query.
      $result = $this->client->search($query);
    }

    // Build the response.
    $response = [
      'results' => [],
      'total' => $result['hits']['total'],
      'aggs' => [
        'business_types' => $result['aggregations']['business_types']['buckets'],
        'local_authorities' => $result['aggregations']['local_authorities']['buckets'],
        'rating_values' => $result['aggregations']['rating_values']['buckets'],
        'fhis_rating_values' => $result['aggregations']['fhis_rating_values']['buckets'],
        'fhrs_rating_values' => $result['aggregations']['fhis_rating_values']['buckets'],
      ],
    ];

    foreach ($result['hits']['hits'] as $hit) {
      $source_data = $hit['_source'];
      $response['results'][] = $source_data;
    }

    return $response;
  }

  /**
   * Get the list of possible categories in a format suitable for Form API (select and checkboxes elements)
   *
   * @param \Drupal\Core\Language\LanguageInterface $language
   *   The preferred language.
   *
   * @return array
   *   An associated array with the keys being the type of the category and the value suitable for Form API #options parameter.
   */
  public function categories(LanguageInterface $language) {
    $language_code = $language->getId();

    // Define the base query.
    $query = [
      'index' => ['ratings-' . $language_code],
      'size' => 0,
      'body' => [
        'aggs' => [
          'business_types' => [
            'terms' => [
              'field' => 'businesstype.label.keyword',
              'order' => ['_term' => 'asc'],
              'size' => 10000,
            ],
          ],
          'local_authorities' => [
            'terms' => [
              'field' => 'localauthoritycode.label.keyword',
              'order' => ['_term' => 'asc'],
              'size' => 10000,
            ],
          ],
          'rating_values' => [
            'terms' => [
              'field' => 'ratingvalue.keyword',
              'order' => ['_term' => 'asc'],
              'size' => 10000,
            ],
          ],
          'fhis_rating_values' => [
            'terms' => [
              'field' => 'fhis_ratingvalue.keyword',
              'order' => ['_term' => 'asc'],
              'size' => 10000,
            ],
          ],
          'fhrs_rating_values' => [
            'terms' => [
              'field' => 'fhrs_ratingvalue.keyword',
              'order' => ['_term' => 'asc'],
              'size' => 10000,
            ],
          ],
        ],
      ],
    ];

    // Execute the query.
    $result = $this->client->search($query);

    // Build the response.
    $response = [
      'business_types' => $result['aggregations']['business_types']['buckets'],
      'local_authorities' => $result['aggregations']['local_authorities']['buckets'],
      'rating_values' => $result['aggregations']['rating_values']['buckets'],
      'fhis_rating_values' => $result['aggregations']['fhis_rating_values']['buckets'],
      'fhrs_rating_values' => $result['aggregations']['fhrs_rating_values']['buckets'],
    ];

    return $response;
  }

  /**
   * Translate aggregates to options.
   */
  public function aggsToOptions($aggs_bucket = []) {
    $options = [];
    foreach ($aggs_bucket as $a) {
      // Add textual representation for numeric values.
      switch ($a['key']) {
        case '5':
          $value = $a['key'] . ' ' . t('Very good');
          break;

        case '4':
          $value = $a['key'] . ' ' . t('Good');
          break;

        case '3':
          $value = $a['key'] . ' ' . t('Generally satisfactory');
          break;

        case '2':
          $value = $a['key'] . ' ' . t('Improvement necessary');
          break;

        case '1':
          $value = $a['key'] . ' ' . t('Major improvement necessary');
          break;

        case '0':
          $value = $a['key'] . ' ' . t('Urgent improvement necessary');
          break;

        case 'AwaitingInspection':
          // We humans like spaces between wording.
          $value = t('Awaiting Inspection');
          break;

        case 'AwaitingPublication':
          // We humans like spaces between wording.
          $value = t('Awaiting Publication');
          break;

        case 'Exempt':
          // Make this label translatable.
          $value = t('Exempt');
          break;

        default:
          $value = $a['key'];
      }
      $options[$a['key']] = (string) $value;
    }
    return $options;
  }

  /**
   * Private helper function to sort array by another array.
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
