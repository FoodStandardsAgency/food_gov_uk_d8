<?php

/**
 * Implements hook_views_data_alter().
 *
 * Defines Elasticsearch result filters and their plugins.
 */
function fsa_es_views_data_alter(array &$data) {
  $data['elasticsearch_result']['ratings_business_type'] = [
    'title' => t('Business type'),
    'filter' => [
      'id' => 'fsa_ratings_business_type',
    ],
  ];

  $data['elasticsearch_result']['ratings_local_authority'] = [
    'title' => t('Local authority'),
    'filter' => [
      'id' => 'fsa_ratings_local_authority',
    ],
  ];

  $data['elasticsearch_result']['ratings_fhrs_rating_value'] = [
    'title' => t('Hygiene rating (England, Northern Ireland, Wales)'),
    'filter' => [
      'id' => 'fsa_ratings_fhrs_rating_value',
    ],
  ];

  $data['elasticsearch_result']['ratings_fhis_rating_value'] = [
    'title' => t('Hygiene rating (Scotland)'),
    'filter' => [
      'id' => 'fsa_ratings_fhis_rating_value',
    ],
  ];
}
