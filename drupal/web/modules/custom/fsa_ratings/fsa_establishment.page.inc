<?php

/**
 * @file
 * Contains fsa_establishment.page.inc.
 *
 * Page callback for FSA Establishment entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for FSA Establishment templates.
 *
 * Default template: fsa_establishment.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_fsa_establishment(array &$variables) {
  // Fetch FsaEstablishment Entity Object.
  $fsa_establishment = $variables['elements']['#fsa_establishment'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
