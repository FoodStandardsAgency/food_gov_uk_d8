<?php

namespace Drupal\fsa_ratings_import\Plugin\migrate_plus\data_parser;

use Drupal\migrate_plus\Plugin\migrate_plus\data_parser\Json;

/**
 * Obtain JSON data for migration.
 *
 * @DataParser(
 *   id = "fsa_establishment_json",
 *   title = @Translation("JSON Parser for FSA Establishment updates")
 * )
 */
class FsaEstablishmentJson extends Json {

  /**
   * Retrieves the JSON data and returns it as an array.
   *
   * @param string $url
   *   URL of a JSON feed.
   *
   * @return array
   *   The selected data to be iterated.
   *
   * @throws \GuzzleHttp\Exception\RequestException
   */
  protected function getSourceData($url) {
    $response = $this->getDataFetcherPlugin()->getResponseContent($url);

    // Convert objects to associative arrays.
    $source_data = json_decode($response, TRUE);

    // If json_decode() has returned NULL, it might be that the data isn't
    // valid utf8 - see http://php.net/manual/en/function.json-decode.php#86997.
    if (is_null($source_data)) {
      $utf8response = utf8_encode($response);
      $source_data = json_decode($utf8response);
    }

    // Wrap the result to an array for migrate to parse from it.
    $source_data = [0 => $source_data];

    $modified_data = $this->prepareRows($source_data);

    return $modified_data;
  }

  /**
   * Modify any of the rows in the file.
   *
   * Any class that implement FsaEstablishmentJson can simply declare protected
   * prepareRows function and massage the data as needed before returning it.
   *
   * @param array $source_data
   *   Array of data.
   *
   * @return array
   *   Modified data.
   */
  protected function prepareRows(array $source_data) {
    return $source_data;
  }

}
