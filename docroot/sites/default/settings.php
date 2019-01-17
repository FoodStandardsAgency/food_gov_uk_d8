<?php

/**
 * Load services definition file - can be overridden below.
 */
$settings['container_yamls'][] = __DIR__ . '/services.yml';

/**
 * General settings.php for all environments.
 * You could use this to add general settings to be used for all environments.
 */

// Acquia Cloud requires this file to allow access to environment variables.
if (file_exists('/var/www/site-php')) {
  require '/var/www/site-php/foodgovuk/foodgovuk-settings.inc';
}
// Acquia Memcache settings.
if ($_ENV['AH_SITE_ENVIRONMENT'] && file_exists($app_root . '/' . $site_path . '/cloud-memcache-d8.php')) {
  require $app_root . '/' . $site_path . '/cloud-memcache-d8.php';
}

# Private filesystem
if (isset($_ENV['AH_SITE_ENVIRONMENT'])) {
  $settings['file_private_path'] = '/mnt/files/' . $_ENV['AH_SITE_GROUP'] . '.' . $_ENV['AH_SITE_ENVIRONMENT'] . '/' . $site_path . '/files-private';
  $config['system.file']['path']['temporary'] = '/tmp'; //"/mnt/gfs/{$_ENV['AH_SITE_GROUP']}.{$_ENV['AH_SITE_ENVIRONMENT']}/tmp";
}
else {
  $settings['file_private_path'] = '{PATH}';
}

/**
 * Drupal core string overrides for FSA setup.
 */
$settings['locale_custom_strings_en'][''] = [
  'You have just used your one-time login link. It is no longer necessary to use this link to log in. Please change your password.' => 'You have just used your one-time login link. Please set yourself a password that you can use to log in again.',
];

$settings['hash_salt'] = 'B081u6MDeLm3bRi5niieR-797DOulNMA-SGCoprrcy5Gjn-hDNAkiy1k8Pnb9y8n1zSXWu4aQQ';

// Disallow configuration changes by default.
$settings['config_readonly'] = TRUE;

// The config names that are allowed to be changed in readonly environments.
$settings['config_readonly_whitelist_patterns'] = [
  'acquia_connector.*',
  'system.site',
  'system.menu.*',
  'system.performance',
  'core.menu.static_menu_link_overrides',
  'config.fsa_ratings',
  'config.fsa_consultations',
  'force_password_change.settings',
  'fsa_content_reminder.settings',
  'webform.webform.food_fraud_crime',
  'webform.webform.food_poisoning',
  'webform.webform.foreign_object',
  'webform.webform.poor_hygiene_practices',
];

// Allow configuration changes via drush (command line).
if (PHP_SAPI === 'cli') {
  $settings['config_readonly'] = FALSE;
}

// Be sure to have config_split.dev disabled by default.
$config['config_split.config_split.dev']['status'] = FALSE;

if (isset($_ENV['AH_SITE_ENVIRONMENT'])) {
  // Either 'dev', 'test', 'prod' or 'ra'.
  $env = $_ENV['AH_SITE_ENVIRONMENT'];
}
else {
  // WKV_ENV_SITE is a legacy environment indicator from WunderTools
  $env = getenv('WKV_SITE_ENV');
}

// Shield config.
$config['shield.settings']['credentials']['shield']['user'] = getenv('HTTP_AUTH_USER');
$config['shield.settings']['credentials']['shield']['pass'] = getenv('HTTP_AUTH_PWD');

// Stage file proxy origin.
$config['stage_file_proxy.settings']['origin'] = 'https://www.food.gov.uk';

// SMTP settings: from environment variables.
$config['smtp.settings']['smtp_host']     = getenv('SMTP_HOST');
$config['smtp.settings']['smtp_port']     = getenv('SMTP_PORT');
$config['smtp.settings']['smtp_username'] = getenv('SMTP_USERNAME');
$config['smtp.settings']['smtp_password'] = getenv('SMTP_PASSWORD');
$config['smtp.settings']['smtp_from']     = getenv('SMTP_FROM');

switch ($env) {
  case 'prod':
    $settings['container_yamls'][] = $app_root . '/' . $site_path . '/prod.services.yml';
    $settings['simple_environment_indicator'] = '#d4000f Production';

    // GTM Environment overrides.
    $config['google_tag.settings']['environment_id'] = 'env-2';
    $config['google_tag.settings']['environment_token'] = 'qiSnyllzn5flpcJTEzjYGA';

    // Elasticsearch.
    $config['elasticsearch_helper.settings']['elasticsearch_helper']['scheme'] = 'https';
    $config['elasticsearch_helper.settings']['elasticsearch_helper']['host'] = 'search-food-gov-uk-website-a4nksv2ajieqlock7l4p6wpasy.eu-west-2.es.amazonaws.com';
    $config['elasticsearch_helper.settings']['elasticsearch_helper']['port'] = '443';

    // Memcache.
    $settings['cache']['default'] = 'cache.backend.memcache';

    // Disable Shield on prod by setting the shield user variable to NULL
    $config['shield.settings']['credentials']['shield']['user'] = NULL;

    break;

  case 'dev':
    $settings['container_yamls'][] = $app_root . '/' . $site_path . '/dev.services.yml';
    $settings['simple_environment_indicator'] = '#004984 Development';

    // GTM Environment overrides.
    $config['google_tag.settings']['environment_id'] = 'env-6';
    $config['google_tag.settings']['environment_token'] = '4d3H88TmNOCwXVDx0PK8bg';

    // Memcache.
    $settings['cache']['default'] = 'cache.backend.memcache';

    break;

  case 'test':
    // Now known as stage on Acquia Cloud platform, but machine key is 'test'.
    $settings['container_yamls'][] = $app_root . '/' . $site_path . '/stage.services.yml';
    $settings['simple_environment_indicator'] = '#e56716 Stage';

    // GTM Environment overrides.
    $config['google_tag.settings']['environment_id'] = 'env-5';
    $config['google_tag.settings']['environment_token'] = 'nNEwJ_lItnO48_pabdUErg';

    // Memcache.
    $settings['cache']['default'] = 'cache.backend.memcache';

    break;

  case 'local':
    $settings['simple_environment_indicator'] = '#88b700 Local';

    $config['config_split.config_split.dev']['status'] = TRUE;
    $settings['config_readonly'] = FALSE;

    // GTM Environment (below values should be as default configurations).
    $config['google_tag.settings']['environment_id'] = 'env-7';
    $config['google_tag.settings']['environment_token'] = 'a4fGxt3oZ4lNeD1SjVDqdA';

    $config['elasticsearch_helper.settings']['elasticsearch_helper']['host'] = 'elasticsearch';

    // Disable Acquia module config.
    $config['acquia_connector.settings']['subscription_data']['active'] = FALSE;
    $config['acquia_connector.settings']['subscription_data']['href'] = NULL;
    $config['acquia_connector.settings']['subscription_data']['uuid'] = NULL;
    $config['purge.plugins']['purgers'] = [];

    // Disable Shield by setting the shield user variable to NULL.
    $config['shield.settings']['credentials']['shield']['user'] = NULL;
    // Disable SMTP.
    $config['smtp.settings']['smtp_on'] = FALSE;

    // Memcache.
    $settings['cache']['default'] = 'cache.backend.memcache';
    $settings['memcache']['servers'] = ['memcached:11211' => 'default'];

    break;
}

// CD / On-Demand environments.
if (preg_match('/(ode\d+)/', $env)) {
  $settings['container_yamls'][] = $app_root . '/' . $site_path . '/stage.services.yml';

  // Memcache.
  $settings['cache']['default'] = 'cache.backend.memcache';
}

/**
 * Location of the site configuration files.
 */
$config_directories = array(
  CONFIG_SYNC_DIRECTORY => '../config/default',
);

/**
 * Trusted hosts patterns.
 */
$settings['trusted_host_patterns'] = [
  'food\.gov\.uk$',
  'foodgovuk\.prod\.acquia-sites\.com',
  'foodgovukstg\.prod\.acquia-sites\.com',
  'foodgovukdev\.prod\.acquia-sites\.com',
  'foodgovukra\.prod\.acquia-sites\.com',
  'foodgovukode\d+\.prod\.acquia-sites\.com',
];

/**
 * Access control for update.php script.
 */
$settings['update_free_access'] = FALSE;
$settings['install_profile'] = 'standard';

// Automatically generated include for settings managed by ddev.
if (file_exists($app_root . '/' . $site_path . '/settings.ddev.php')) {
  include $app_root . '/' . $site_path . '/settings.ddev.php';
}

/**
 * Allow local settings override.
 */
if (file_exists(__DIR__ . '/settings.local.php')) {
  include __DIR__ . '/settings.local.php';
}
