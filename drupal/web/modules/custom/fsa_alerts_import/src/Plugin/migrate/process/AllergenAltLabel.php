<?php

namespace Drupal\fsa_alerts_import\Plugin\migrate\process;

use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;
use GuzzleHttp\Exception\RequestException;
use Drupal\Component\Serialization\Json;

/**
 * Store allergen alternative label from extended API.
 *
 * @todo: we seem to start duplicate code, combine this with allergen_description plugin.
 *
 * @MigrateProcessPlugin(
 *   id = "allergen_alt_label",
 * )
 */
class AllergenAltLabel extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $description = '';
    if (isset($value)) {
      // Get API base URL, config may use directory, take only domain part.
      $config = \Drupal::config('config.fsa_alerts_import');
      $parse = parse_url($config->get('api_url'));
      $url = $parse['scheme'] . '://' . $parse['host'] . '/codes/alerts/def/allergen/' . $value . '?_format=jsonld';

      $client = \Drupal::httpClient();
      try {
        $res = $client->get($url);
        $body = Json::decode($res->getBody());
        $description = $body['skos:altLabel']['@value'];
      }
      catch (RequestException $e) {
        \Drupal::logger('fsa_alerts_import')->warning(
          $this->t('Failed getting allergen alternative label: @msg', ['@msg' => $e])
        );
      }
    }

    return $description;
  }

}
