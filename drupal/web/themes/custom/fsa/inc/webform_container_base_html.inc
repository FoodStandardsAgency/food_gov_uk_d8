<?php

/**
 * @file
 * Includes FSA theme webform container base html preprocess functionality.
 */

/**
 * Implements template_preprocess_webform_container_base_html().
 *
 * @param array $variables
 *   Fieldset element.
 */
function fsa_preprocess_webform_container_base_html(&$variables) {

  // Get audience value.
  if (isset($variables['webform_submission'])) {
    $audience = $variables['webform_submission']->getElementData('audience');
  }
  else {
    $audience = FALSE;
  }

  // Get fieldset identifier.
  if (isset($variables['value']['#id'])) {
    $id = $variables['value']['#id'];
  }
  else {
    $id = FALSE;
  }

  // Conditionally remove fieldset.
  $con_bus = $audience == 'consumer' && $id == 'food_fraud_crime--business';
  $bus_con = $audience == 'business' && $id == 'food_fraud_crime--consumer';
  if ($con_bus || $bus_con) {
    $variables = [];
  }

}
