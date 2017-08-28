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

    // Get content_type taxonomy term and set as body class.
    $tid = $node->get('field_content_type')->target_id;
    if (is_numeric($tid)) {
      $term = Term::load($tid);

      // Add content type based html class.
      $variables['attributes']['class'][] = 'theme--content-type-' . strtolower(Html::cleanCssIdentifier($term->getName()));
    }
  }

  // @todo: before we know how content structures rely on paths to set body classes.
  $uri_segments = explode('/', parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

  // Help centre/contact us pages.
  if ($uri_segments[1] === 'help') {
    $variables['attributes']['class'][] = 'theme-service';
    $variables['attributes']['class'][] = 'theme-service--help-centre';
  }

  // FHRS
  if ($uri_segments[1] === 'ratings' || $uri_segments[1] === 'fsa-ratings') {
    $variables['attributes']['class'][] = 'theme-service';
    $variables['attributes']['class'][] = 'theme-service--fhrs';
  }

}
