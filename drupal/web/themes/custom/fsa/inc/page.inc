<?php

/**
 * @file
 * Includes FSA theme page preprocess functionality.
 */

/**
 * Implements template_preprocess_page().
 */
function fsa_preprocess_page(&$variables) {
  $block = \Drupal\block\Entity\Block::load('languageswitcher');
  $variables['language_switcher'] = \Drupal::entityTypeManager()
    ->getViewBuilder('block')
    ->view($block);
}
