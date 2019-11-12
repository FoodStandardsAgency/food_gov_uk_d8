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

  /**
   * Traverses Alert's siblings and retrieves an array of previous alert
   * notations.
   *
   * @param $previous_alert_notation string
   * @return array
   */
  public static function getNodePreviousAlerts($previous_alert_notation) {
    $previous_notations = [];
    $has_previous_alert = TRUE;

    // Loop to traverse through previous alerts.
    while ($has_previous_alert) {
      // Load alert node via notation field.
      $query = \Drupal::entityQuery('node')
        ->condition('type', 'alert')
        ->condition('field_alert_notation', $previous_alert_notation);

      $nid = $query->execute();

      // Exit loop if no alert found.
      if (empty($nid)) {
        $has_previous_alert = FALSE;
      }
      else {
        // Load previous alert node.
        $nid = reset($nid);
        $previous_alert = \Drupal::entityTypeManager()
          ->getStorage('node')->load($nid);

        $previous_alert_notation = $previous_alert->field_alert_previous->value;

        if (isset($previous_alert_notation)) {
          $previous_notations[] = $previous_alert_notation;
        }
      }
    }

    return $previous_notations;
  }

}
