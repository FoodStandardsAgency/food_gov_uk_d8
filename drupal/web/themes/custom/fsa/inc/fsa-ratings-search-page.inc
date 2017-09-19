<?php

/**
 * @file
 * Includes FSA theme fsa ratings search page preprocess functionality.
 */

/**
 * Implements template_preprocess_page().
 */
function fsa_preprocess_fsa_ratings_search_page(&$variables) {
  $variables['current_language'] = \Drupal::languageManager()->getCurrentLanguage()->getId();
}
