<?php

/**
 * @file
 * Elastic search configuration.
 */

$config['elasticsearch_helper.settings']['elasticsearch_helper']['host'] = getenv('DB_HOST_DRUPAL');

if (SETTINGS_PLATFORM === D_PLATFORM_ACQUIA) {
  $config['elasticsearch_helper.settings']['elasticsearch_helper']['scheme'] = 'https';
  $config['elasticsearch_helper.settings']['elasticsearch_helper']['host'] = getenv('ELASTIC_SEARCH_HOST');
  $config['elasticsearch_helper.settings']['elasticsearch_helper']['port'] = '443';
}
elseif (SETTINGS_ENVIRONMENT === D_ENVIRONMENT_LOCAL) {
  $config['elasticsearch_helper.settings']['elasticsearch_helper']['host'] = 'elasticsearch';
}
