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

  const RATINGS_API_MAX_PAGE_SIZE = 5000;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, MigrationInterface $migration) {

    $count = FhrsApiController::totalCount();
    $page_count = $count / self::RATINGS_API_MAX_PAGE_SIZE;
    $start_at_page = 1;
    $page_size = self::RATINGS_API_MAX_PAGE_SIZE;

    $import_mode = \Drupal::config('fsa_ratings_import')->get('import_mode');
    // For development we'll want to have less processing and import to happen
    if ($import_mode == 'development') {
      // Choose random page to be imported
      $start_at_page = rand(100, $page_count-100);
      // Import only one page of 100 items
      $page_count = $start_at_page;
      $page_size = 100;
    }

    // Use the Url plugin provided config option.
    $configuration['urls'] = [];
    for ($i = $start_at_page; $i <= $page_count; $i++) {
      $configuration['urls'][] = $configuration['base_url'] . '?pageSize=' . $page_size . '&pageNumber=' . $i;
    }

    // Pass in the URL's to fetch the content from.
    $this->sourceUrls = $configuration['urls'];

    parent::__construct($configuration, $plugin_id, $plugin_definition, $migration);
  }

}
