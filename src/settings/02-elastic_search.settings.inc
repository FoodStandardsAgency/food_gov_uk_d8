<?php

/**
 * @file
 * Elastic search configuration.
 */

$config['elasticsearch_helper.settings']['elasticsearch_helper']['host'] = getenv('DB_HOST_DRUPAL');

if (SETTINGS_ENVIRONMENT === D_ENVIRONMENT_PROD) {
  $config['elasticsearch_helper.settings']['elasticsearch_helper']['host'] = '10.2.3.85';
}
elseif (SETTINGS_ENVIRONMENT === D_ENVIRONMENT_LOCAL) {
  $config['elasticsearch_helper.settings']['elasticsearch_helper']['host'] = 'elasticsearch';
}
