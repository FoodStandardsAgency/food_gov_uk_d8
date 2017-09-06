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
    $tid = ($node->hasField('field_content_type')) ? $node->get('field_content_type')->target_id : FALSE;
    if ($tid !== FALSE) {
      $term = Term::load($tid);

      // Add content type based html class.
      $variables['attributes']['class'][] = 'theme--content-type-' . strtolower(Html::cleanCssIdentifier($term->getName()));

      // Add body classes based on selected taxonomy term
      switch ($term->id()) {
        case 27: // Help
          $variables['attributes']['class'][] = 'theme-service';
          $variables['attributes']['class'][] = 'theme-service--help-centre';
          break;
      }
    }
  }

  // Rely on URL aliases for FHRS ratings section.
  $uri_segments = explode('/', parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
  if ($uri_segments[1] === 'ratings' || $uri_segments[1] === 'fsa-ratings') {
    $variables['attributes']['class'][] = 'theme-service';
    $variables['attributes']['class'][] = 'theme-service--fhrs';
  }

}
