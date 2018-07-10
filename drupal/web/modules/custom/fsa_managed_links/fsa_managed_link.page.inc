<?php

/**
 * @file
 * Contains fsa_managed_link.page.inc.
 *
 * Page callback for FSA Managed Link entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for FSA Managed Link templates.
 *
 * Default template: fsa_managed_link.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_fsa_managed_link(array &$variables) {
  // Fetch FsaManagedLink Entity Object.
  $fsa_managed_link = $variables['elements']['#fsa_managed_link'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
