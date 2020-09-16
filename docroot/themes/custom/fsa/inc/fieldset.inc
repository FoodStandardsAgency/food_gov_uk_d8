<?php

/**
 * @file
 */

/**
 * Implements hook_preprocess_HOOK().
 */
function fsa_preprocess_fieldset(&$variables) {

  $add_attrs = [
    'edit-type',
    'edit-type--2',
    'edit-audience',
    'edit-audience--2',
    'edit-topic',
    'edit-topic--2',
    'edit-region',
    'edit-region--2',
    'edit-fhrs-rating-value--11',
    'edit-fhis-rating-value--11',
    'edit-consultation-year',
    'edit-consultation-year--2',
    'edit-consultation-status',
    'edit-consultation-status--2',
    'edit-consultation-responses-published',
    'edit-consultation-responses-published--2',
    'edit-year',
    'edit-year--2',
    'edit-status',
    'edit-status--2',
    'edit-responses-published',
    'edit-responses-published--2'
  ];

  $variables['toggle_fieldset'] = FALSE;

  if (in_array($variables['element']['#id'], $add_attrs)) {
    $id = 'fieldset-' . $variables['element']['#id'];
    $variables['toggle_fieldset'] = TRUE;
    $variables['fieldset_wrapper_id'] = $id;

    $variables['legend']['attributes']['data-state'] = ['is-open'];
    $variables['legend']['attributes']['data-theme'] = ['dynamic'];
    $variables['legend']['attributes']['data-state-element'] = ['#' . $id];
    $variables['legend']['attributes']['aria-expanded'] = ['false'];
    $variables['legend']['attributes']['aria-controls'] = [$id];
    $variables['legend']['attributes']['class'] = ['toggle-button', 'toggle-fieldset__button'];
  }
}
