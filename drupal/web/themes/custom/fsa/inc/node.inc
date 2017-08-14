<?php

/**
 * @file
 * Includes FSA theme node preprocess functionality.
 */

/**
 * Implements hook_preprocess_node().
 */
function fsa_preprocess_node(&$variables) {

  // Set vars for easy access
  $node = $variables['node'];

  if ($variables['teaser']) {
    // Override title if it is set on the respective field.
    if (isset($node->field_liftup_title->value) && $node->field_liftup_title->value != '') {
      $variables['label'] = $node->field_liftup_title->value;
    }
  }
}