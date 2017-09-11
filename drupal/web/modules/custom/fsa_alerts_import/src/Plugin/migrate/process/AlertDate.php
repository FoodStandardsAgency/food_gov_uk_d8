<?php

namespace Drupal\fsa_alerts_import\Plugin\migrate\process;

use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;

/**
 * Store dates without the timezone.
 *
 * @MigrateProcessPlugin(
 *   id = "alert_date",
 * )
 */
class AlertDate extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {

    // Convert date to not include the timezone (prevent failure with date
    // field length).
    $date = date('Y-m-d\TH:i:s', strtotime($value));

    return $date;
  }

}
