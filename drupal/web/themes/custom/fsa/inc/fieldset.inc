<?php

/**
 * Implements hook_preprocess_HOOK().
 */
function fsa_preprocess_fieldset(&$variables) {

  $add_attrs = [
    'edit-audience',
    'edit-region',
    'edit-fhrs-rating-value--11',
    'edit-fhis-rating-value--11',
  ];

  $variables['toggle_fieldset'] = false;

  if (in_array($variables['element']['#id'], $add_attrs)) {
    $id = 'fieldset-' . $variables['element']['#id'];
    $variables['toggle_fieldset'] = true;
    $variables['fieldset_wrapper_id'] = $id;

    $variables['legend']['attributes']['data-state'] = ['is-open'];
    $variables['legend']['attributes']['data-theme'] = ['dynamic'];
    $variables['legend']['attributes']['data-state-element'] = ['#' . $id];
    $variables['legend']['attributes']['aria-expanded'] = ['false'];
    $variables['legend']['attributes']['aria-controls'] = [$id];
    $variables['legend']['attributes']['class'] = ['toggle-button', 'toggle-fieldset__button'];
  }
}