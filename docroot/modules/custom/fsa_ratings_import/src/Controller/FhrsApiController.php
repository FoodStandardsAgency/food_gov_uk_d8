<?php

namespace Drupal\fsa_ratings_import\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Drupal\Component\Serialization\Json;
use Drupal\Component\Utility\UrlHelper;

/**
 * Class FhrsApiController.
 *
 * @package Drupal\fhrs_api\Controller
 */
class FhrsApiController extends ControllerBase {

  const RATINGS_API_MAX_PAGE_SIZE = 5000;

  // The default time window to fetch updates from.
  const FSA_RATING_UPDATE_SINCE = '-1 week';

  // FHRS API base URL.
  // @todo: USING FHRS STAGING UNTIL API UPDATES ARE IN THEIR PRODUCTION.
  const FSA_FHRS_API_URL = 'http://staging-api.ratings.food.gov.uk/';

  /**
   * FHRS API base URL.
   *
   * @return string
   *   The API base url.
   */
  protected function baseUrl() {
    $url = self::FSA_FHRS_API_URL;
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
   *   Array of filters to build a querystring.
   *
   * @return int
   *   Total count
   */
  public static function totalCount(array $filters = []) {

    // Get only one item from API to get the meta (reduces the response time).
    $filters['pageSize'] = 1;

    // Take filters and build a query for the API.
    $query = UrlHelper::buildQuery($filters);

    $url = FhrsApiController::baseUrl() . 'Establishments?' . $query;

    // Add FHRS required headers.
    $headers = FhrsApiController::headers();

    $client = \Drupal::httpClient();
    try {
      $res = $client->get($url, $headers);
      $count = Json::decode($res->getBody());
      $count = $count['meta']['totalCount'];
      return $count;
    }
    catch (RequestException $e) {
      \Drupal::logger('fsa_ratings_import')->error('Failed getting totalcount from the API: ' . $e);
      return FALSE;
    }
  }

  /**
   * Build array of URL's to get establishments updates.
   *
   * Default time to get updates from is the previous week.
   * Use a drupal state to override the updatedSince parameter:
   * @code drush sset fsa_rating_import.updated_since "2018-01-30"
   *
   * @return array|bool
   *   Array of urls.
   */
  public static function getUrlForItemsToUpdate() {

    $since_default = self::FSA_RATING_UPDATE_SINCE;

    // Get the updatedSince timestamp from a state (validation is performed.
    $since = \Drupal::state()->get('fsa_rating_import.updated_since');
    if (!isset($since) || !is_int(strtotime($since))) {
      // Be sure we have proper time from the state and fallback to default.
      $since = $since_default;
    }

    $since = strtotime($since);
    $sinceDate = date("Y-m-d", $since);
    $nowDate = date("Y-m-d", strtotime('now'));

    $query = UrlHelper::buildQuery(['updatedSince' => $sinceDate]);
    $url = FhrsApiController::baseUrl() . 'Establishments/basic?' . $query;

    // Add FHRS required headers.
    $headers = FhrsApiController::headers();

    $client = \Drupal::httpClient();
    try {
      $urls = [];
      $res = $client->get($url, $headers);
      $body = Json::decode($res->getBody());

      if (!empty($body['establishmentsExtended'])) {
        $establishments = $body['establishmentsExtended'];
        foreach ($establishments as $establishment) {
          $urls[] = FhrsApiController::baseUrl() . 'Establishments/' . $establishment['FHRSID'];
        }

        // Log the attempt.
        \Drupal::logger('fsa_ratings_import')->info('FHRS API: Update from ' . $url);

        return $urls;
      }
      else {
        \Drupal::logger('fsa_ratings_import')->notice('FHRS API: No establishment updates between ' . $sinceDate . ' and ' . $nowDate);
        return FALSE;
      }

    }
    catch (RequestException $e) {
      \Drupal::logger('fsa_ratings_import')->error('FHRS API: Failed getting establishment updates: <pre>' . $e . '</pre>');
      return FALSE;
    }
  }

  /**
   * Returns max page size.
   *
   * @return int
   *   Max page size value.
   */
  public function getMaxPageSize() {
    return self::RATINGS_API_MAX_PAGE_SIZE;
  }

  /**
   * Returns total number of pages.
   *
   * @param array $filters
   *   Existing filters.
   *
   * @return int
   *   Rounded to int value for total pages.
   */
  public function pagesTotal(array $filters = []) {
    $result = self::totalCount($filters) / self::RATINGS_API_MAX_PAGE_SIZE;
    // 4.5 means 5 pages.
    return ceil($result);
  }

  /**
   * Prepares fetch URL.
   *
   * @param int $page_number
   *   Page number.
   * @param int $page_size
   *   Page size.
   * @param array $options
   *   Options array.
   *
   * @return string
   *   URI for fetch URL.
   */
  public function getFetchUrl($page_number = 1, $page_size = 5000, array $options = []) {
    return Url::fromUri(sprintf('%s/Establishments/basic/%d/%d', $this->baseUrl(), $page_number, $page_size), $options)->toString();
  }

  /**
   * Fetches the results from API.
   *
   * @param int $page_number
   *   Page number.
   * @param int $page_size
   *   Page size.
   * @param array $options
   *   Options array.
   *
   * @return bool|\Psr\Http\Message\ResponseInterface
   *   Results from the API or FALSE if a problem.
   */
  public function fetch($page_number = 1, $page_size = 5000, array $options = []) {
    // Get URL.
    $url = $this->getFetchUrl($page_number, $page_size, $options);

    try {
      return \Drupal::httpClient()->get($url, $this->headers());
    }
    catch (RequestException $e) {
      return FALSE;
    }
  }

}
