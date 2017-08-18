<?php

/**
 * @file
 * Includes FSA theme node preprocess functionality.
 */

/**
 * Implements template_preprocess_node().
 */
function fsa_preprocess_node(&$variables) {

  // Set vars for easy access.
  $node = $variables['node'];

  if ($variables['teaser']) {
    // Override title if it is set on the respective field.
    if (isset($node->field_liftup_title->value) && $node->field_liftup_title->value != '') {
      $variables['label'] = $node->field_liftup_title->value;
    }

    // Replace teaser body value with summary (even if no summary set).
    if (isset($variables['content']['body']['#items'])) {
      $variables['content']['body'][0]['#text'] = $variables['content']['body']['#items']->getValue()[0]['summary'];
    }
  }
}
