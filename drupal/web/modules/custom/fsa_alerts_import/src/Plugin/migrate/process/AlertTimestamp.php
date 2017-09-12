<?php

namespace Drupal\fsa_alerts_import\Plugin\migrate\process;

use Drupal\Component\Datetime\Time;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;

/**
 * Store dates as timestamp.
 *
 * @MigrateProcessPlugin(
 *   id = "alert_timestamp",
 * )
 */
class AlertTimestamp extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {

    // Convert to non-standard date format to proper timestamp.
    $timestamp = strtotime($value);

    // If strtotime() failed use current time.
    if ($timestamp == '') {
      $timestamp = \Drupal::time()->getCurrentTime();
    }

    return $timestamp;
  }

}
