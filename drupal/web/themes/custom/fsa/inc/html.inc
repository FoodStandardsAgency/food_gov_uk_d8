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

    // Set node type as theme-class
    $variables['attributes']['class'][] = 'theme--' . $node->getType();
    if ($node->getType() == 'news' || $node->getType() == 'alert') {
      // Additionally add combined class for news and alert node types.
      $variables['attributes']['class'][] = 'theme--news-alerts';
    }

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
        case 29: // News & Alerts
          $variables['attributes']['class'][] = 'theme--news-alerts';
          break;
      }
    }
  }

  // Rely on URL paths for FHRS ratings and subscribe sections.
  $current_path = \Drupal::service('path.current')->getPath();
  $uri_segments = explode('/', $current_path);
  if ($uri_segments[1] === 'ratings' || $uri_segments[1] === 'fsa-ratings') {
    $variables['attributes']['class'][] = 'theme--service';
    $variables['attributes']['class'][] = 'theme--fhrs';
  }


  // Get service routes to set body classes for the section.
  $service_urls = [];
  $routes = [
    'fsa_signin.default_controller_signInPage',
    'fsa_signin.default_controller_myAccountPage',
  ];
  foreach ($routes AS $route) {
    try {
      $service_url = \Drupal\Core\Url::fromRoute($route)->toString();
      $service_url = explode('/', $service_url);
      $service_urls[] = $service_url[1];
    }
    catch (\Exception $e) {
      // Just do nothing.
    }
  }
  if (in_array($uri_segments[1], $service_urls)) {
    $variables['attributes']['class'][] = 'theme--subscribe';
  }

}
