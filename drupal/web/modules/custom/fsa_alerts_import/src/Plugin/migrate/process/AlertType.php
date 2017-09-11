<?php

namespace Drupal\fsa_alerts_import\Plugin\migrate\process;

use Drupal\fsa_alerts_import\AlertImportHelpers;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;

/**
 * Stores fixed alert type to the select field.
 *
 * @MigrateProcessPlugin(
 *   id = "alert_type",
 * )
 */
class AlertType extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {

    // Get the Alert type from URI.
    $type = AlertImportHelpers::getIdFromUri($value);

    switch ($type) {
      case 'Alert':
        // Do not store "Alert" type, all items have that anyway.
        return '';
          break;
      default:
        // Return one of the expected type variation.
        return $type;
    }
  }
}
