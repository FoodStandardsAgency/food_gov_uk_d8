<?php

namespace Drupal\fsa_ratings_import\Plugin\migrate\source;

use Drupal\fsa_ratings_import\Controller\FhrsApiController;
use Drupal\migrate_plus\Plugin\migrate\source\Url;
use Drupal\migrate\Plugin\MigrationInterface;
use Drupal\Component\Utility\UrlHelper;

/**
 * Source plugin for retrieving data via URLs.
 *
 * FHRS API return different format for timestamped query and full updates,
 * this plugin handles both. Full import can be toggled with a drupal stage.
 *
 * Enable full import:
 * @code drush sset fsa_rating_import.full_import 1
 * Disable full import:
 * @code drush sdel fsa_rating_import.full_import
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

    // By default we should be pulling updates only. Use a state to toggle the
    // full update mode (see documentation).
    $full_import = \Drupal::state()->get('fsa_rating_import.full_import');
    if ($full_import) {
      $update = FALSE;
    }
    else {
      $update = TRUE;
    }

    if ($update) {
      // We can think 0 as the root level.
      $configuration['item_selector'] = '0';

      // Change json parser in configuration to our custom hacky parser.
      $configuration['data_parser_plugin'] = 'fsa_establishment_json';

      // Build the array of url's to fetch the content from.
      $configuration['urls'] = FhrsApiController::getUrlForItemsToUpdate();
    }
    else {
      $filters = [];
      $start_at_page = 1;
      $process = $migration->getProcess();
      if ($process['langcode'][0]['default_value'] == 'cy') {
        // For welsh language migration limit establishments to only those
        // located in Wales (countryID 4).
        $filters['countryId'] = 4;
        $count = FhrsApiController::totalCount($filters);
      }
      else {
        $count = FhrsApiController::totalCount();
      }

      $page_count = $count / self::RATINGS_API_MAX_PAGE_SIZE;
      $page_size = self::RATINGS_API_MAX_PAGE_SIZE;

      $import_mode = \Drupal::config('fsa_ratings_import')->get('import_mode');
      $import_random = \Drupal::config('fsa_ratings_import')->get('import_random');
      // For development we'll want to have less processing + import.
      if ($import_mode == 'development') {
        // Add $config['fsa_ratings_import']['import_random'] = TRUE; to your
        // local settings.local.php to choose random page to be imported.
        if ($import_random) {
          $start_at_page = rand(100, $page_count - 100);
        }
        else {
          // First page(s) provide rather ugly content.
          $start_at_page = 1;
        }

        // Import only one page of 100 items.
        $page_count = $start_at_page;
        $page_size = 100;
      }

      // Append pageSize to filters.
      $filters['pageSize'] = $page_size;

      // And build query for API calls.
      $query = UrlHelper::buildQuery($filters);

      // Use the Url plugin provided config option.
      $configuration['urls'] = [];

      for ($i = $start_at_page; $i <= $page_count; $i++) {
        // Append pageNumber "manually", calling buildQuery() again for every
        // item would double the excecution time.
        $configuration['urls'][] = $configuration['base_url'] . '?' . $query . '&pageNumber=' . $i;
      }
    }

    // Pass in the URL's to fetch the content from.
    $this->sourceUrls = $configuration['urls'];

    parent::__construct($configuration, $plugin_id, $plugin_definition, $migration);
  }

}
