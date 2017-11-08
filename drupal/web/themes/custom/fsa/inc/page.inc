<?php

/**
 * @file
 * Includes FSA theme page preprocess functionality.
 */

/**
 * Implements template_preprocess_page().
 */
function fsa_preprocess_page(&$variables) {
  $language = \Drupal::languageManager()->getCurrentLanguage()->getId();
  $variables['current_language'] = $language;
  if ($language == 'cy') {
    $variables['home_url'] = '/cy';
  }
  else {
    $variables['home_url'] = '/';
  }
}
