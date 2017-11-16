<?php

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

    drupal_set_message($row->getSourceProperty('FHRSID'));

    $fhis = 'field_fhis_ratingvalue';
    $fhrs = 'field_fhrs_ratingvalue';

    switch ($row->getSourceProperty('SchemeType')) {
      case 'FHIS':
        $row->setDestinationProperty($fhis, $value);
        $row->setEmptyDestinationProperty($fhrs);
        break;

      default:
        $row->setDestinationProperty($fhrs, $value);
        $row->setEmptyDestinationProperty($fhis);
        break;

    }

    return $value;
  }

}
