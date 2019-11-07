<?php

namespace Drupal\fsa_alerts_import\Plugin\migrate\process;

use Drupal\Component\Serialization\Json;
use Drupal\fsa_alerts_import\AlertImportHelpers;
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

    // Get API URL (base path).
    $config = \Drupal::config('config.fsa_alerts_import');
    $api_base_path = $config->get('api_url');

    // The URL to a single alert.
    $api_url = $api_base_path . '/id/' . $value;

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

    $row->setDestinationProperty('field_nation',
      $this->getNationsCids($item['country']));

    // Map previous alert, store only alert ID.
    if (isset($item['previousAlert'])) {
      $prev = AlertImportHelpers::getIdFromUri($item['previousAlert']['@id']);
      $row->setDestinationProperty('field_alert_previous_multiple', $prev);
    }

    // Map single textfield values.
    $mapping = [
      'SMStext' => 'field_alert_smstext',
      'consumerAdvice' => 'field_alert_consumeradvice',
      'actionTaken' => 'field_alert_actiontaken',
      'description' => 'field_alert_description',
    ];
    // Map single-values.
    foreach ($mapping as $key => $field) {

      if (is_string($item[$key])) {
        $row->setDestinationProperty($field, $item[$key]);
      }
      else {
        $row->setDestinationProperty($field, '');
      }
    }

    // Store reportingBusiness & otherBusiness values.
    // Value can be an array or single value.
    $businessname = [];
    $mapping = [
      'reportingBusiness' => 'field_alert_reportingbusiness',
      'otherBusiness' => 'field_alert_otherbusiness',
    ];
    foreach ($mapping as $key => $field) {
      if (isset($item[$key])) {
        if (isset($item[$key][0])) {
          // Multiple values.
          foreach ($item[$key] as $business) {
            $businessname[] = $business['commonName'];
          }
        }
        else {
          // Single-value.
          $businessname = [$item[$key]['commonName']];
        }

        $row->setDestinationProperty($field, $businessname);
      }
    }

    // Store productdetails as raw json until we know what kind of data
    // the details will store.
    if ($productDetails = Json::encode($item['productDetails'])) {
      $row->setDestinationProperty('field_alert_productdetails_raw', $productDetails);
    }

    // Map problem/riskStatement.
    if (isset($item['problem'][0]['riskStatement'])) {
      $riskStatement = $item['problem'][0]['riskStatement'];
      $row->setDestinationProperty('field_alert_riskstatement', $riskStatement);
    }

    // Map to allergens by notation.
    if (isset($item['problem'][0]['allergen'])) {
      $tids = [];
      foreach ($item['problem'][0]['allergen'] as $key => $field) {
        // We don't know the tid, map allergen terms with notation field match.
        $term_name = $field['notation'];
        $term = \Drupal::entityTypeManager()
          ->getStorage('taxonomy_term')
          ->loadByProperties([
            'vid' => 'alerts_allergen',
            'field_alert_notation' => $term_name,
          ]);

        foreach ($term as $a => $b) {
          $tids[] = $a;
        }
        $row->setDestinationProperty('field_alert_allergen', $tids);
      }
    }

    // Store relatedMedia.
    if (isset($item['relatedMedia'])) {
      $media_link = [];
      foreach ($item['relatedMedia'] as $media) {
        $media_link[] = [
          'uri' => $media['@id'],
          'title' => $media['title'],
        ];
      }
      $row->setDestinationProperty('field_alert_relatedmedia', $media_link);
    }

    // Return the actual notation value.
    return $value;
  }

  /**
   * Mapping nations IDs to taxonomy term.
   * 
   * @param array $item
   *
   * @return array
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  private function getNationsCids($countries = NULL) {
    $nations = [
      'GB-ENG' => 'England',
      'GB-WLS' => 'Wales',
      'GB-NIR' => 'Northern Ireland',
      'GB-SCT' => 'Scotland',
    ];

    $properties = [
      'name' => $nations,
      'vid' => 'nation',
    ];

    $terms = \Drupal::entityManager()
      ->getStorage('taxonomy_term')
      ->loadByProperties($properties);
    $nations_tids = [];
    foreach ($terms as $term) {
      if ($key = array_search($term->getName(), $nations)) {
        $nations_tids[$key] = $term->id();
      }
    }

    // Loop through the countries and store nation term id's to array.
    if (isset($countries)) {
      $cids = [];
      foreach ($countries as $country) {
        $cid = AlertImportHelpers::getIdFromUri($country['@id']);
        if (!empty($nations_tids[$cid])) {
          $cids[] = $nations_tids[$cid];
        }
      }
    }
    else {
      // In case API returns no country values set all as that is default for
      // all new content on the site.
      $cids = array_values($nations_tids);
    }
    return $cids;
  }

}
