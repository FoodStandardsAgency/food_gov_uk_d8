<?php

namespace Drupal\fsa_ratings_import\Controller;

use Drupal\Core\Controller\ControllerBase;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Drupal\Component\Serialization\Json;

/**
 * Class FhrsApiController.
 *
 * @package Drupal\fhrs_api\Controller
 */
class FhrsApiController extends ControllerBase {

  /**
   * FHRS API base URL.
   *
   * @return string
   *   The API base url.
   */
  protected function baseUrl() {
    // @todo: Move URL to configs
    $url = 'http://api.ratings.food.gov.uk/';
    return $url;
  }

  /**
   * Add FHRS API required headers.
   *
   * @return array
   *   Headers
   */
  protected function headers() {
    $headers = [
      'headers' => [
        'accept' => 'application/json',
        'x-api-version' => 2,
      ],
    ];
    return $headers;
  }

  /**
   * Get status code of FHRS API.
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup|int
   *   Status code or error.
   */
  public static function status() {

    $client = new Client();
    try {
      $res = $client->get(FhrsApiController::baseUrl(), ['http_errors' => FALSE]);
      return($res->getStatusCode());
    }
    catch (RequestException $e) {
      return t('Status check failed');
    }
  }

  /**
   * Get total count of FHRS items.
   *
   * @param array $filters
   *   Array of filters.
   *
   * @return int
   *   Total count
   */
  public static function totalCount(array $filters = []) {

    $url = FhrsApiController::baseUrl() . 'Establishments?pageSize=1&name=' . $filters['name'] . '&ratingKey=' . $filters['rating'];
    $headers = FhrsApiController::headers();

    $client = \Drupal::httpClient();
    try {
      $res = $client->get($url, $headers);
      $count = Json::decode($res->getBody());
      $count = $count['meta']['totalCount'];
      return $count;
    }
    catch (RequestException $e) {
      return FALSE;
    }
  }

}
