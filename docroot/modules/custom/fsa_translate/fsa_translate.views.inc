<?php

/**
 * @file
 * Implements views hooks.
 */

/**
 * Implements hook_views_data().
 */
function fsa_translate_views_data() {

  // Register tracking table with views.
  $data['fsa_translation_required']['table']['group'] = t('Translation required');
  $data['fsa_translation_required']['table']['base'] = [
    'title' => t('Translation required'),
    'help' => t('Tracks nodes requiring translation.'),
  ];

  // Add translation required field.
  $data['fsa_translation_required']['translation_required'] = [
    'title' => t('Translation required'),
    'help' => t('Indicates if a node requires translation.'),
    'field' => [
      'id' => 'boolean',
    ],
    'sort' => [
      'id' => 'standard',
    ],
    'filter' => [
      'id' => 'boolean',
      'label' => t('Translation required'),
    ],
    'argument' => [
      'id' => 'numeric',
    ],
  ];

  // Establish join between tracking and nodes.
  $data['fsa_translation_required']['table']['join'] = [
    'node_field_data' => [
      'left_field' => 'nid',
      'field' => 'nid',
    ],
  ];

  // Enable relationship with nodes.
  $data['fsa_translation_required']['nid'] = [
    'title' => t('Translations required'),
    'help' => t('Relate translations required to node content.'),
    'field' => [
      'id' => 'standard',
    ],
    'filter' => [
      'id' => 'numeric',
    ],
    'argument' => [
      'id' => 'numeric',
    ],
    'relationship' => [
      'base' => 'node_field_data',
      'base field' => 'nid',
      'id' => 'standard',
      'label' => t('Translation required'),
    ],
  ];
  return $data;
}
