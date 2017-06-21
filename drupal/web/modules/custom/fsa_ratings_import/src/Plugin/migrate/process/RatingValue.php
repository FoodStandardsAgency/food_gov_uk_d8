<?php
/**
 * @file
 * Contains Drupal\fsa_ratings_import\Plugin\migrate\process\RatingValue.
 */

namespace Drupal\fsa_ratings_import\Plugin\migrate\process;

use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;

/**
 * Choose to save the ratingValue to either FHRS or FHIS field.
 *
 * @MigrateProcessPlugin(
 *   id = "rating_value",
 * )
 */
class RatingValue extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    if (is_numeric($value)) {
      // FHRS entries are always numeric.
      $row->setDestinationProperty('field_fhrs_ratingvalue', $value);
    }
    elseif (is_string($value)) {
      // FHIS entries are strings.
      $row->setDestinationProperty('field_fhis_ratingvalue', $value);
    }

    return $value;
  }

}
