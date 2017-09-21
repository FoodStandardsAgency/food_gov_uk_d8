<?php

/**
 * @file
 * FSA custom views data.
 *
 * @see Drupal\fsa_custom\Plugin\Views\Filter
 */

/**
 * Implements hook_views_data_alter().
 */
function fsa_custom_views_data_alter(array &$data) {
  $data['node_field_data']['year_filter'] = array(
    'title' => t('Year filter'),
    'filter' => array(
      'title' => t('Year filter'),
      'help' => t('Provides a custom filter for content by year.'),
      'field' => 'created',
      'id' => 'year_select',
    ),
  );
}
