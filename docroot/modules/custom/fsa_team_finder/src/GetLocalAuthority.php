<?php

namespace Drupal\fsa_team_finder;

use Drupal\Component\Serialization\Json;
use GuzzleHttp\Exception\RequestException;

/**
 * Provides local authority ONS code and name.
 */
class GetLocalAuthority {

  /**
   * Gets data.
   *
   * @param string $query
   *   Query fragment.
   *
   * @return array
   *   Council details.
   *
   * @see https://mapit.mysociety.org/docs/
   */
  public function get($query) {

    // Build mapit request.
    $base = 'https://mapit.mysociety.org';
    $postcode = str_replace(' ', '', $query);
    $config = \Drupal::config('config.mapit_api_key');
    $key = $config->get('mapit_api_key');
    $url = $base . '/postcode/' . $postcode . '?api_key=' . $key;

    // Call mapit.
    $client = \Drupal::httpClient();
    $client->request('GET', $url, ['http_errors' => FALSE]);
    try {
      $response = $client->get($url);
      $data = Json::decode($response->getBody()->getContents());
    }
    catch (RequestException $e) {
      watchdog_exception('fsa_team_finder', $e);
      return [];
    }
    if (isset($data['shortcuts']['council'])) {

      // Negotiate two-tier local government.
      if (!is_array($data['shortcuts']['council'])) {
        $council = $data['shortcuts']['council'];
      }
      elseif (isset($data['shortcuts']['council']['district'])) {
        $council = $data['shortcuts']['council']['district'];
      }
      else {
        return [];
      }
    }
    else {
      return [];
    }
    return [
      'name' => $data['areas'][$council]['name'],
      'mapit_area' => $council,
    ];
  }

}
