<?php

namespace Drupal\fsa_alerts_import;

/**
 * @file
 * Contains \Drupal\fsa_alerts_import\AlertImportHelpers.
 */

/**
 * Alert import helpers controller.
 */
class AlertImportHelpers {

  /**
   * Get id from an URI resource.
   *
   * @param string $uri
   *   Fully qualified URI string.
   *
   * @return array
   *   Array of id's
   */
  public static function getIdFromUri($uri) {

    $ids = [];
    $uri = rtrim($uri, '/');
    // Get last segment from the resource (should work even if entry !URL).
    preg_match('/([^\/]*)$/', $uri, $ids);

    return $ids[0];

  }

}
