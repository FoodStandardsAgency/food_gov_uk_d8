<?php

use Drupal\taxonomy\Entity\Term;
use Drupal\Component\Utility\Html;

/**
 * @file
 * Includes FSA theme html preprocess functionality.
 */

/**
 * Implements of template_preprocess_html().
 */
function fsa_preprocess_html(&$variables) {

  // Do magic when we have a node object.
  $node = \Drupal::routeMatch()->getParameter('node');
  if (is_object($node)) {

    $variables['attributes']['class'][] = 'node-' . $node->id();
    $variables['attributes']['class'][] = 'theme--' . $node->getType();

    // Get content_type taxonomy term and set as body class.
    $tid = ($node->hasField('field_content_type')) ? $node->get('field_content_type')->target_id : 0;
    if ($tid != NULL) {
      $term = Term::load($tid);

      // Add content type based html class.
      $variables['attributes']['class'][] = 'theme--content-type-' . strtolower(Html::cleanCssIdentifier($term->getName()));

      // Add body classes based on selected taxonomy term
      switch ($term->id()) {
        case 27: // Help
          $variables['attributes']['class'][] = 'theme--service';
          $variables['attributes']['class'][] = 'theme--help-centre';
          break;
      }
    }
  }

  // Rely on URL paths for FHRS ratings section.
  $current_path = \Drupal::service('path.current')->getPath();
  $uri_segments = explode('/', $current_path);
  if ($uri_segments[1] === 'ratings' || $uri_segments[1] === 'fsa-ratings') {
    $variables['attributes']['class'][] = 'theme--service';
    $variables['attributes']['class'][] = 'theme--fhrs';
  }

}
