<?php

/**
 * @file
 * Includes FSA theme html preprocess functionality.
 */

use Drupal\Core\Url;
use Drupal\taxonomy\Entity\Term;
use Drupal\Component\Utility\Html;

/**
 * Implements of template_preprocess_html().
 */
function fsa_preprocess_html(&$variables) {
  $routematch = \Drupal::routeMatch();
  $routename = $routematch->getRouteName();
  $user = \Drupal::currentUser();

  if ($user->isAuthenticated()) {
    $variables['attributes']['class'][] = 'logged-in';
  }

  // Add class(es) if logged in.
  if ($user->hasPermission('edit content')) {
    $variables['attributes']['class'][] = 'has-cms-toolbar';
  }

  // Add class to body if frontpage.
  $is_front = \Drupal::service('path.matcher')->isFrontPage();
  if ($is_front) {
    $variables['attributes']['class'][] = 'theme--frontpage';
  }

  // Set body class for term and related listing pages.
  if (in_array($routename, ['view.a_to_z.page_1', 'entity.taxonomy_term.canonical'])) {
    $variables['attributes']['class'][] = 'theme--term';
  }

  // Send classes to theme error pages.
  if (in_array($routename, ['system.401', 'system.403', 'system.404'])) {
    $variables['attributes']['class'][] = 'theme--error';
    $variables['attributes']['class'][] = 'theme--error-' . strtolower(Html::cleanCssIdentifier($routename));
  }

  // Do magic when we have a node object.
  $node = $routematch->getParameter('node');
  if (is_object($node)) {

    $variables['attributes']['class'][] = 'node-' . $node->id();

    // Set node type as theme-class.
    $variables['attributes']['class'][] = 'theme--' . $node->getType();
    if ($node->getType() == 'news' || $node->getType() == 'alert') {
      // Additionally add combined class for news and alert node types.
      $variables['attributes']['class'][] = 'theme--news-alerts';
    }

    // Get content_type taxonomy term and set as body class.
    $tid = ($node->hasField('field_content_type')) ? $node->get('field_content_type')->target_id : 0;
    if (is_numeric($tid) && $term = Term::load($tid)) {

      // Add content type based html class.
      $variables['attributes']['class'][] = 'theme--content-type-' . strtolower(Html::cleanCssIdentifier($term->getName()));

      // Add body classes based on selected taxonomy term.
      switch ($term->id()) {
        // Help.
        case 27:
          $variables['attributes']['class'][] = 'theme--service';
          $variables['attributes']['class'][] = 'theme--help-centre';
          break;

        // News & Alerts.
        case 29:
          $variables['attributes']['class'][] = 'theme--news-alerts';
          break;
      }
    }
  }

  // Rely on URL paths for FHRS ratings and subscribe sections.
  $current_path = \Drupal::service('path.current')->getPath();
  $uri_segments = explode('/', $current_path);
  if ($uri_segments[1] === 'hygiene-ratings') {
    $variables['attributes']['class'][] = 'theme--service';
    $variables['attributes']['class'][] = 'theme--fhrs';
  }

  // Search page(s) body class.
  if ($uri_segments[1] === 'search' || ($uri_segments[1] === 'news-alerts' && $uri_segments[2] == 'search')) {
    $variables['attributes']['class'][] = 'theme--search';
  }

  // Get service routes to set body classes for the section.
  $service_urls = [];
  $routes = [
    'fsa_signin.default_controller_signInPage',
    'fsa_signin.default_controller_myAccountPage',
    'fsa_signin.default_controller_accountSettingsPage'
  ];
  foreach ($routes as $route) {
    try {
      $language = \Drupal::languageManager()->getCurrentLanguage()->getId();
      $service_url = Url::fromRoute($route)->toString();
      $service_url = explode('/', $service_url);
      $service_urls[] = $language === 'en' ? $service_url[1] : $service_url[2];
    }
    catch (\Exception $e) {
      // Just do nothing.
    }
  }
  if (in_array($uri_segments[1], $service_urls)) {
    $variables['attributes']['class'][] = 'theme--subscribe';
  }
}
