<?php

namespace Drupal\fsa_ratings_import\Plugin\migrate\source;

use Drupal\fsa_ratings_import\Controller\FhrsApiController;
use Drupal\migrate_plus\Plugin\migrate\source\Url;
use Drupal\migrate\Plugin\MigrationInterface;

/**
 * Source plugin for retrieving data via URLs.
 *
 * @MigrateSource(
 *   id = "establishment_api_url"
 * )
 */
class EstablishmentApiUrl extends Url {

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, MigrationInterface $migration) {

    $count = FhrsApiController::totalCount();

    $pagesize = 5000;
    $length = $count/$pagesize;
    $calls = 0;

    // Use the Url plugin provided config option.
    $configuration['urls'] = [];
    for ($i = 1; $i <= $length; $i++) {
      $configuration['urls'][] = $configuration['base_url'] . '?pageSize=' . $pagesize . '&pageNumber=' . $i;
//      $calls++;
    }

//    var_dump((int) $length);
//    var_dump($calls);
//    $configuration['urls'][] = $configuration['base_url'] . '?pageSize=2&pageNumber=1';

    // Pass in the URL's to fetch the content from.
    $this->sourceUrls = $configuration['urls'];

    parent::__construct($configuration, $plugin_id, $plugin_definition, $migration);
  }

}
