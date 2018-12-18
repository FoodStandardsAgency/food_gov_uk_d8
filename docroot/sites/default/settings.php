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

$config['elasticsearch_helper.settings']['elasticsearch_helper']['host'] = getenv('DB_HOST_DRUPAL');

if (isset($_ENV['AH_SITE_ENVIRONMENT'])) {
  // Either 'dev', 'test', 'prod' or 'ra'.
  $env = $_ENV['AH_SITE_ENVIRONMENT'];
}
else {
  // WKV_ENV_SITE is a legacy environment indicator from WunderTools
  $env = getenv('WKV_SITE_ENV');
}

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

    // Warden settings.
    // Shared secret between the site and Warden server.
    $config['warden.settings']['warden_token'] = 'aCsTnss8YMAPzU6COnaYKFcr7BTiir';
    // Location of your Warden server. No trailing slash.
    $config['warden.settings']['warden_server_host_path'] = 'https://warden.wunder.io';
    // Allow external callbacks to the site. When set to FALSE pressing refresh site
    // data in Warden will not work.
    $config['warden.settings']['warden_allow_requests'] = TRUE;
    // Basic HTTP authorization credentials.
    $config['warden.settings']['warden_http_username'] = 'warden';
    $config['warden.settings']['warden_http_password'] = 'wunder';
    // IP address of the Warden server. Only these IP addresses will be allowed to
    // make callback # requests.
    $config['warden.settings']['warden_public_allow_ips'] = '83.136.254.41,2a04:3541:1000:500:d456:61ff:fee3:7d8d';
    // Define module locations.
    $config['warden.settings']['warden_preg_match_custom'] = '{^modules\/custom\/*}';
    $config['warden.settings']['warden_preg_match_contrib'] = '{^modules\/contrib\/*}';

    break;

  case 'dev':
    $settings['container_yamls'][] = $app_root . '/' . $site_path . '/dev.services.yml';
    $settings['simple_environment_indicator'] = '#004984 Development';

    // GTM Environment overrides.
    $config['google_tag.settings']['environment_id'] = 'env-6';
    $config['google_tag.settings']['environment_token'] = '4d3H88TmNOCwXVDx0PK8bg';

    // Shield config.
    $config['shield.settings']['user'] = 'fsauser';
    $config['shield.settings']['pass'] = 'FCeDh4u&7n2p';

    break;

  case 'test':
    // Now known as stage on Acquia Cloud platform, but machine key is 'test'.
    $settings['container_yamls'][] = $app_root . '/' . $site_path . '/stage.services.yml';
    $settings['simple_environment_indicator'] = '#e56716 Stage';

    // GTM Environment overrides.
    $config['google_tag.settings']['environment_id'] = 'env-5';
    $config['google_tag.settings']['environment_token'] = 'nNEwJ_lItnO48_pabdUErg';

    // Shield config.
    $config['shield.settings']['user'] = 'fsauser';
    $config['shield.settings']['pass'] = 'FCeDh4u&7n2p';

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

    // Stage file proxy origin.
    $config['stage_file_proxy.settings']['origin'] = 'https://www.food.gov.uk';

    $settings['memcache']['servers'] = ['memcache:11211' => 'default'];

    break;
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
