<?php

/**
 * @file
 * Contains fsa_authority.page.inc.
 *
 * Page callback for FSA Authority entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for FSA Authority templates.
 *
 * Default template: fsa_authority.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_fsa_authority(array &$variables) {
  // Fetch FsaAuthority Entity Object.
  $fsa_authority = $variables['elements']['#fsa_authority'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
