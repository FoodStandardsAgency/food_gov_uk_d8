<?php

namespace Drupal\fsa_alerts_import\Plugin\migrate\process;

use Drupal\fsa_alerts_import\AlertImportHelpers;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;

/**
 * Get allergen term parent.
 *
 * @MigrateProcessPlugin(
 *   id = "allergen_parent",
 * )
 */
class AllergenParent extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {

    // Get term notation.
    $parent = AlertImportHelpers::getIdFromUri($value);
    if ($parent != '') {
      $term_name = $parent;
      $term = \Drupal::entityTypeManager()
        ->getStorage('taxonomy_term')
        ->loadByProperties([
          'vid' => 'alerts_allergen',
          'name' => $term_name,
        ]);

      $tid = key($term);
    }
    else {
      $tid = FALSE;
    }

    return $tid;
  }

}
