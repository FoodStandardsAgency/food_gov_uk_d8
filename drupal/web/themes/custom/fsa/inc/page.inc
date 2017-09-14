<?php

/**
 * @file
 * Includes FSA theme page preprocess functionality.
 */

/**
 * Implements template_preprocess_page().
 */
function fsa_preprocess_page(&$variables) {

  // current language
  $variables['current_language'] = \Drupal::languageManager()->getCurrentLanguage()->getId();
}
