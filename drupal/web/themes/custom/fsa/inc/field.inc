<?php

/**
 * @file
 * Includes FSA theme field preprocess functionality.
 */

/**
 * Implements template_preprocess_field().
 */
function fsa_preprocess_field(&$variables) {

  // Set classes for node entity fields.
  if ($variables['entity_type'] == 'node' && isset($variables['field_name'])) {
    $variables['attributes']['class'][] = 'field__' . $variables['field_name'];
  }

}
