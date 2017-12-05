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

  if (isset($variables['webform_submission']) && isset($variables['value']['#id'])) {
    if ($audience = $variables['webform_submission']->getElementData('audience')) {

      // Get fieldset identifier.
      $id = $variables['value']['#id'];

      // Conditionally remove fieldset.
      $con_bus = $audience == 'consumer' && $id == 'food_fraud_crime--business';
      $bus_con = $audience == 'business' && $id == 'food_fraud_crime--consumer';
      if ($con_bus || $bus_con) {
        $variables = [];
      }
    }
  }

}
