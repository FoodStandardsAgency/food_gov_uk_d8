<?php

/**
 * @file
 * FSA ES views hook implementations.
 */

/**
 * Implements hook_views_data_alter().
 *
 * Defines Elasticsearch result filters and their plugins.
 */
function fsa_es_views_data_alter(array &$data) {
  $data['elasticsearch_result']['keyword'] = [
    'title' => t('Keywords'),
    'filter' => [
      'id' => 'fsa_keyword',
    ],
    'argument' => [
      'id' => 'string',
    ],
  ];

  $data['elasticsearch_result']['ratings_business_type'] = [
    'title' => t('Business type'),
    'help' => t('Ratings business type'),
    'filter' => [
      'id' => 'fsa_ratings_business_type',
    ],
  ];

  $data['elasticsearch_result']['ratings_local_authority'] = [
    'title' => t('Local authority'),
    'help' => t('Ratings local authority'),
    'filter' => [
      'id' => 'fsa_ratings_local_authority',
    ],
  ];

  $data['elasticsearch_result']['ratings_fhrs_rating_value'] = [
    'title' => t('Hygiene rating (England, Northern Ireland, Wales)'),
    'help' => t('Ratings hygiene rating'),
    'filter' => [
      'id' => 'fsa_ratings_fhrs_rating_value',
    ],
  ];

  $data['elasticsearch_result']['ratings_fhis_rating_value'] = [
    'title' => t('Hygiene rating (Scotland)'),
    'help' => t('Ratings hygiene rating'),
    'filter' => [
      'id' => 'fsa_ratings_fhis_rating_value',
    ],
  ];

  $data['elasticsearch_result']['guidance_content_type'] = [
    'title' => t('Content type'),
    'help' => t('Guidance content type'),
    'filter' => [
      'id' => 'fsa_guidance_content_type',
    ],
  ];

  $data['elasticsearch_result']['guidance_audience'] = [
    'title' => t('Audience'),
    'help' => t('Guidance audience'),
    'filter' => [
      'id' => 'fsa_guidance_audience',
    ],
  ];

  $data['elasticsearch_result']['nation'] = [
    'title' => t('Region'),
    'filter' => [
      'id' => 'fsa_nation',
    ],
  ];

  $data['elasticsearch_result']['news_alerts_type'] = [
    'title' => t('Type'),
    'help' => t('News and alerts type'),
    'filter' => [
      'id' => 'fsa_news_alerts_type',
    ],
  ];

  $data['elasticsearch_result']['news_type'] = [
    'title' => t('News type'),
    'help' => t('News type'),
    'filter' => [
      'id' => 'fsa_news_type',
    ],
  ];

  $data['elasticsearch_result']['consultation_status'] = [
    'title' => t('Consultation status'),
    'help' => t('Consultation status'),
    'filter' => [
      'id' => 'fsa_consultation_status',
    ],
  ];

  $data['elasticsearch_result']['consultation_responses_published'] = [
    'title' => t('Responses published'),
    'help' => t('Consultation responses published'),
    'filter' => [
      'id' => 'fsa_consultation_responses_published',
    ],
  ];

  $data['elasticsearch_result']['consultation_year'] = [
    'title' => t('Year'),
    'help' => t('Consultation start and close year'),
    'filter' => [
      'id' => 'fsa_consultation_year',
    ],
  ];

  $data['elasticsearch_result']['research_topic'] = [
    'title' => t('Research topic'),
    'help' => t('Research topic'),
    'filter' => [
      'id' => 'fsa_research_topic',
    ],
  ];

  $data['elasticsearch_result']['evidence_type'] = [
    'title' => t('Evidence type'),
    'help' => t('Evidence type'),
    'filter' => [
      'id' => 'fsa_evidence_type',
    ],
  ];

}
