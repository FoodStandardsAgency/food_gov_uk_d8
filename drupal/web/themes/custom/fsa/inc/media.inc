<?php
/**
 * @file
 * Includes FSA theme media preprocess functionality.
 */

use Drupal\Component\Utility\Html;

/**
 * Implements template_preprocess_media().
 */
function fsa_preprocess_media(&$variables) {

  // Pass regional classes for document link display mode template.
  if (isset($variables['content']['field_document']['#object']) && $variables['content']['field_document']['#view_mode'] == 'document_link') {
    $regions = $variables['content']['field_document']['#object']->field_nation->view();

    if (isset($regions[0]) && empty($regions[1])) {
      // Add region-specific class when only one region.
      $variables['attributes']['class'][] = 'regional-variation';
      $variables['attributes']['class'][] = 'regional-variation--' . strtolower(Html::cleanCssIdentifier($regions[0]['#title']));
    }
    elseif (isset($regions[1]) && empty($regions[2])) {
      // Set as "multi-region".
      $variables['attributes']['class'][] = 'regional-variation';
      $variables['attributes']['class'][] = 'regional-variation--multi-region';
    }
  }
}
