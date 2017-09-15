<?php

/**
 * @file
 * Includes FSA theme field preprocess functionality.
 */

/**
 * Implements template_preprocess_field().
 */
function fsa_preprocess_field(&$variables) {

  if ($variables['label_display'] == 'inline') {
    // Force span on inline label display title_element.
    $variables['title_element'] = 'span';
  }
  else if ($variables['element']['#bundle'] == 'alert') {
    // All (non-inline) alert node labels forced to h3.
    $variables['title_element'] = 'h3';
  }
  else {
    // Define (non-inline) title_element per field here, defaults to 'h2'.
    switch ($variables['field_name']) {
      case 'field_update_date':
        $variables['title_element'] = 'div';
        break;
      default:
        $variables['title_element'] = 'h2';
        break;
    }
  }

  // Set fields classes.
  if (isset($variables['field_name'])) {
    $field_name = 'field__' . $variables['field_name'];
    $variables['attributes']['class'][] = $field_name;
    $variables['title_attributes']['class'][] = $field_name . '__heading';
    $variables['title_attributes']['class'][] = 'label__' . $variables['label_display'];

    // Add counting classes for fields with multiple items.
    foreach(array_keys($variables['items']) as $delta) {
      $variables['items'][$delta]['attributes']->setAttribute('class', $field_name);
      $variables['items'][$delta]['attributes']->setAttribute('class', $field_name . '__item' . ' ' . $field_name . '__item--' . $delta);
    }
  }
}
