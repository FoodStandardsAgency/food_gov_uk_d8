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
  elseif ($variables['element']['#bundle'] == 'alert') {
    // All (non-inline) alert node labels forced to h3.
    $variables['title_element'] = 'h2';

    switch ($variables['field_name']) {
      case 'field_alert_relatedmedia':
        // Fake alerts related media to use same class as field_document.
        $variables['attributes']['class'][] = 'field__field_document';
        break;
    }
  }
  else {
    // Define (non-inline) title_element per field here, defaults to 'h2'.
    switch ($variables['field_name']) {
      case 'field_update_date':
        $variables['title_element'] = 'div';
        break;

      case 'field_additional_document':
        $variables['title_element'] = 'h4';
        break;

      case 'field_link_list':
        $variables['title_element'] = 'h3';
        break;

      default:
        $variables['title_element'] = 'h2';
        break;
    }
  }

  // Set fields classes.
  if (isset($variables['field_name'])) {
    $field_name = 'field__' . $variables['field_name'];
    $variables['attributes']['class'][] = 'field';
    $variables['attributes']['class'][] = $field_name;
    $variables['title_attributes']['class'][] = $field_name . '__heading';
    $variables['title_attributes']['class'][] = 'label__' . $variables['label_display'];

    // Add counting classes for fields with multiple items.
    foreach (array_keys($variables['items']) as $delta) {
      $variables['items'][$delta]['attributes']->setAttribute('class', $field_name);
      $variables['items'][$delta]['attributes']->setAttribute('class', $field_name . '__item' . ' ' . $field_name . '__item--' . ($delta % 2 == 0 ? 'even' : 'odd'));
    }
  }

  if ($variables['element']['#field_name'] == 'field_news_type') {
    foreach ($variables['items'] as $key => $item) {
      if ($key == 0) {
        // Show generic "News" label instead of the type.
        $variables['items'][$key]['content']["#plain_text"] = t('News');
      }
      else {
        // And unset other news types from page display.
        unset($variables['items'][$key]);
      }
    }
  }

}

/**
 * Implements template_preprocess_form_element_label().
 */
function fsa_preprocess_form_element_label(&$vars) {
  // Translate Region options to cy when necessary.
  $langcode = 'cy';
  if ((!empty($vars["element"]["#id"]))
    && (strpos($vars["element"]["#id"], 'edit-region') === 0)
    && (\Drupal::languageManager()->getCurrentLanguage()->getId() === $langcode)) {
    $term_name = trim(strip_tags($vars["title"]["#markup"]));
    if (($terms = \Drupal::entityTypeManager()
        ->getStorage('taxonomy_term')
        ->loadByProperties(['name' => $term_name]))
      && ($term = current($terms))
      && ($term->hasTranslation($langcode))
      && ($term_tr = $term->getTranslation($langcode))) {
      $vars["title"]["#markup"] = $term_tr->label();
    }
  }
}