<?php

namespace Drupal\fsa_alerts_import\Plugin\migrate\process;

use Drupal\Component\Serialization\Json;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;
use GuzzleHttp\Exception\RequestException;

/**
 * Stores individual alert properties from API resource URI provided json.
 *
 * @MigrateProcessPlugin(
 *   id = "alert_item_properties",
 * )
 */
class AlertItemProperties extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $item = [];

    // API URL to single alert, @todo: consider storing base_path to var/config.
    $api_url = 'http://fsa-staging-alerts.epimorphics.net/food-alerts/id/' . $value;

    try {
      $client = \Drupal::httpClient();
      $response = $client->get(
        $api_url
      );

      $item = Json::decode($response->getBody());

      // API always return only one item here, set item for easy access.
      $item = $item['items'][0];

    }
    catch (RequestException $exception) {
      // Log failure(s) to fetch individual alert data.
      \Drupal::logger('fsa_alerts')->warning(t('Failed to fetch Alert properties: "%error"', ['%error' => $exception->getMessage()]));
    }

    // Map single textfield values.
    $mapping = [
      'SMStext' => 'field_alert_smstext',
      'actionTaken' => 'field_alert_actiontaken',
      'description' => 'field_alert_description',
    ];
    // Map single-values.
    foreach ($mapping as $key => $field) {
      if (is_string($item[$key])) {
        $row->setDestinationProperty($field, $item[$key]);
      }
    }

    // Store reportingBusiness & otherBusiness values.
    // Value can be an array or single value.
    $business = [];
    $mapping = [
      'reportingBusiness' => 'field_alert_reportingbusiness',
      'otherBusiness' => 'field_alert_otherbusiness',
    ];
    foreach ($mapping as $key => $field) {
      if (isset($item[$key])) {
        if (isset($item[$key][0])) {
          // Multiple values.
          foreach ($item[$key] as $item) {
            $business[] = $item['commonName'];
          }
        }
        else {
          // Single-value.
          $business = [$item[$key]['commonName']];
        }

        $row->setDestinationProperty($field, $business);
      }
    }

    // Store productdetails as raw json until we know what kind of data
    // the details will store.
    if ($productDetails = Json::encode($item['productDetails'])) {
      $row->setDestinationProperty('field_alert_productdetails_raw', $productDetails);
    }

    // field_alert_allergen
    // field_alert_relatedmedia
    // field_alert_reportingbusiness.
    // Return the actual notation value.
    return $value;
  }

}
