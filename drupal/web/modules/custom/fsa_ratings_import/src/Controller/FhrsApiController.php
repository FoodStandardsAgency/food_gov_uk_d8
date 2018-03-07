<?php

namespace Drupal\fsa_ratings_import\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Drupal\Component\Serialization\Json;
use Drupal\Component\Utility\UrlHelper;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class FhrsApiController.
 *
 * @package Drupal\fhrs_api\Controller
 */
class FhrsApiController extends ControllerBase {

  /** Max result per page size. */
  const RATINGS_API_MAX_PAGE_SIZE = 5000;

  /** @var \GuzzleHttp\Client $httpClient */
  protected $httpClient;

  /**
   * FhrsApiController constructor.
   *
   * @param \GuzzleHttp\Client $http_client
   */
  public function __construct(Client $http_client) {
    $this->httpClient = $http_client;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('http_client')
    );
  }

  /**
   * FHRS API base URL.
   *
   * @return string
   *   The API base url.
   */
  public function baseUrl() {
    // @todo: Move URL to configs
    $url = 'http://api.ratings.food.gov.uk';
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
   * Returns max page size.
   *
   * @return int
   */
  public function getMaxPageSize() {
    return self::RATINGS_API_MAX_PAGE_SIZE;
  }

  /**
   * Get status code of FHRS API.
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup|int
   *   Status code or error.
   */
  public function status() {

    try {
      $res = $this->httpClient->get($this->baseUrl(), ['http_errors' => FALSE]);
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
  public function totalCount(array $filters = []) {

    // Get only one item from API to get the meta (reduces the response time).
    $filters['pageSize'] = 1;

    // Take filters and build a query for the API.
    $query = UrlHelper::buildQuery($filters);

    $url = $this->baseUrl() . '/Establishments?' . $query;

    // Add FHRS required headers.
    $headers = $this->headers();

    try {
      $res = $this->httpClient->get($url, $headers);
      $count = Json::decode($res->getBody());
      $count = $count['meta']['totalCount'];
      return $count;
    }
    catch (RequestException $e) {
      return FALSE;
    }
  }

  /**
   * Returns total number of pages.
   *
   * @param array $filters
   *
   * @return int
   */
  public function pagesTotal(array $filters = []) {
    $result = $this->totalCount($filters) / $this->getMaxPageSize();
    // 4.5 means 5 pages.
    return ceil($result);
  }

  /**
   * Prepares fetch URL.
   *
   * @param int $page_number
   * @param int $page_size
   * @param array $options
   *
   * @return string
   */
  public function getFetchUrl($page_number = 1, $page_size = 5000, $options = []) {
    return Url::fromUri(sprintf('%s/Establishments/basic/%d/%d', $this->baseUrl(), $page_number, $page_size), $options)->toString();
  }

  /**
   * Fetches the results from API.
   *
   * @param int $page_number
   * @param int $page_size
   * @param array $options
   *
   * @return bool|\Psr\Http\Message\ResponseInterface
   */
  public function fetch($page_number = 1, $page_size = 5000, $options = []) {
    // Get URL.
    $url = $this->getFetchUrl($page_number, $page_size, $options);

    try {
      return $this->httpClient->get($url, $this->headers());
    }
    catch (RequestException $e) {
      return FALSE;
    }
  }

}
