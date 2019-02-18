<?php

namespace Drupal\fsa_ratings_import\Plugin\migrate\process;

use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;

/**
 * Get lat/lon coords from AddressLine1 or PostCode values.
 *
 * @MigrateProcessPlugin(
 *   id = "geocode",
 * )
 */
class GeoCode extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {

    if (!empty($value)) {
      return $value;
    }

    // Ask Google Geocode API for lat/lon values from the postcode value, if we have it.
    $postcode = $row->getSourceProperty('PostCode');
    $address1 = $row->getSourceProperty('AddressLine1');

    $geocode_wkt;
    // A lot of mobile traders put all address data into AddressLine1, try that.
    if (!empty($address1)) {
      $geocode_wkt = $address1;
    }
    // Prioritise postcode, if we have it.
    if (!empty($postcode)) {
      $geocode_wkt = $postcode;
    }

    // Abort if we have no WKT to geocode.
    if (empty($geocode_wkt)) {
      return $value;
    }

    $geocoder_id = 'google_geocoding_api';
    $geocode_result = \Drupal::service('plugin.manager.geolocation.geocoder')
      ->getGeoCoder($geocoder_id)
      ->geocode($geocode_wkt);

    if (!empty($geocode_result['location'])) {
      $row->setDestinationProperty('field_geocode', $geocode_result['location']);
    }
  }

}
