<?php

namespace Drupal\fsa_alerts_import\Plugin\migrate\process;

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

    // @todo: USE AlertImportHelpers::getIdFromUri() to get the types.
    // Alert type(s) are stored to API as array of URI resource.
    $uri = rtrim($value, '/');

    // Get last segment from that resource (should work even if entry !URL)
    preg_match('/([^\/]*)$/', $uri, $types);

    foreach ($types as $type) {
      // Ignore alert type and use only the other stored type.
      if ($type == 'Alert') {
        continue;
      }
      return $type;
    }
  }

}
