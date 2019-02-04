<?php

namespace Drupal\fsa_alerts_import\Plugin\migrate\source;

use Drupal\migrate_plus\Plugin\migrate\source\Url;
use Drupal\migrate\Plugin\MigrationInterface;

/**
 * Source plugin to get FSA Alerts API URL & resource from migration config.
 *
 * @MigrateSource(
 *   id = "fsa_alerts_api_url"
 * )
 */
class FsaAlertsApiUrl extends Url {

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, MigrationInterface $migration) {

    // Get API Resource path from the migration config.
    $api_resource = $configuration['api_resource'];

    // Get API URL from config and set it.
    $config = \Drupal::config('config.fsa_alerts_import');
    $api_base_path = $config->get('api_url');

    $configuration['urls'] = [];

    // Build full Alerts API URL.
    // @todo: Extend to a loop if need to fetch paged alerts.
    $configuration['urls'][] = $api_base_path . $api_resource;

    // Pass API URL(s) for source processor.
    $this->sourceUrls = $configuration['urls'];

    parent::__construct($configuration, $plugin_id, $plugin_definition, $migration);
  }

}
