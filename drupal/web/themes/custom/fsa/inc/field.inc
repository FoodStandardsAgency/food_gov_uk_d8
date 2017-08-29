<?php

/**
 * @file
 * Includes FSA theme field preprocess functionality.
 */

/**
 * Implements template_preprocess_field().
 */
function fsa_preprocess_field(&$variables) {

  // Control the field label element per field, defaults to 'h2'.
  switch ($variables['field_name']) {
    case 'field_update_date':
      $variables['title_element'] = 'div';
      break;
    default:
      $variables['title_element'] = 'h2';
      break;
  }

  // Set classes for node entity fields.
  if ($variables['entity_type'] == 'node' && isset($variables['field_name'])) {
    $field_name = 'field__' . $variables['field_name'];
    $variables['attributes']['class'][] = $field_name;
    $variables['title_attributes']['class'][] = $field_name . '__heading';

    // Add counting classes for fields with multiple items.
    foreach(array_keys($variables['items']) as $delta) {
      $variables['items'][$delta]['attributes']->setAttribute('class', $field_name);
      $variables['items'][$delta]['attributes']->setAttribute('class', $field_name . '__item--' . $delta);
    }
  }
}
