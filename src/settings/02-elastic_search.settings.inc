<?php

/**
 * @file
 * Elastic search configuration.
 */

$config['elasticsearch_helper.settings']['elasticsearch_helper']['host'] = getenv('DB_HOST_DRUPAL');

if (SETTINGS_ENVIRONMENT === D_ENVIRONMENT_PROD) {
  $config['elasticsearch_helper.settings']['elasticsearch_helper']['scheme'] = 'https';
  $config['elasticsearch_helper.settings']['elasticsearch_helper']['host'] = 'search-food-gov-uk-website-a4nksv2ajieqlock7l4p6wpasy.eu-west-2.es.amazonaws.com';
  $config['elasticsearch_helper.settings']['elasticsearch_helper']['port'] = '443';
}
elseif (SETTINGS_ENVIRONMENT === D_ENVIRONMENT_TEST) {
  $config['elasticsearch_helper.settings']['elasticsearch_helper']['scheme'] = 'https';
  $config['elasticsearch_helper.settings']['elasticsearch_helper']['host'] = 'search-food-gov-uk-website-dev-f33m6htrjv6wmka5kzjv3tc62q.eu-west-2.es.amazonaws.com';
  $config['elasticsearch_helper.settings']['elasticsearch_helper']['port'] = '443';

  if (SETTINGS_INSTANCE !== 'foodgovukdev.prod.acquia-sites.com') {
    // Temporary disable elastic search outside of the dev env.
    $config['elasticsearch_helper.settings']['elasticsearch_helper']['host'] = 'localhost';
  }
}
elseif (SETTINGS_ENVIRONMENT === D_ENVIRONMENT_DEV) {
  $config['elasticsearch_helper.settings']['elasticsearch_helper']['scheme'] = 'https';
  $config['elasticsearch_helper.settings']['elasticsearch_helper']['host'] = 'search-food-gov-uk-website-dev-f33m6htrjv6wmka5kzjv3tc62q.eu-west-2.es.amazonaws.com';
  $config['elasticsearch_helper.settings']['elasticsearch_helper']['port'] = '443';
}
elseif (SETTINGS_ENVIRONMENT === D_ENVIRONMENT_LOCAL) {
  $config['elasticsearch_helper.settings']['elasticsearch_helper']['host'] = 'elasticsearch';
}
