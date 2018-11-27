<?php

/**
 * General settings.php for all environments.
 * You could use this to add general settings to be used for all environments.
 */

// Acquia Cloud requires this file to allow access to environment variables.
if (file_exists('/var/www/site-php')) {
  require '/var/www/site-php/foodgovuk/foodgovuk-settings.inc';
}

/**
 * Database settings (overridden per environment)
 */
$databases = array();
$databases['default']['default'] = array (
  'database' => 'drupal',
  'username' => getenv('DB_USER_DRUPAL'),
  'password' => getenv('DB_PASS_DRUPAL'),
  'prefix' => '',
  'host' => getenv('DB_HOST_DRUPAL'),
  'port' => '3306',
  'namespace' => 'Drupal\\Core\\Database\\Driver\\mysql',
  'driver' => 'mysql',
);


/**
 * Drupal core string overrides for FSA setup.
 */
$settings['locale_custom_strings_en'][''] = [
  'You have just used your one-time login link. It is no longer necessary to use this link to log in. Please change your password.' => 'You have just used your one-time login link. Please set yourself a password that you can use to log in again.',
];

$settings['hash_salt'] = 'B081u6MDeLm3bRi5niieR-797DOulNMA-SGCoprrcy5Gjn-hDNAkiy1k8Pnb9y8n1zSXWu4aQQ';

if ( (isset($_SERVER["HTTPS"]) && strtolower($_SERVER["HTTPS"]) == "on")
  || (isset($_SERVER["HTTP_X_FORWARDED_PROTO"]) && $_SERVER["HTTP_X_FORWARDED_PROTO"] == "https")
  || (isset($_SERVER["HTTP_HTTPS"]) && $_SERVER["HTTP_HTTPS"] == "on")
) {
  $_SERVER["HTTPS"] = "on";

  // Tell Drupal we're using HTTPS (url() for one depends on this).
  $settings['https'] = TRUE;
}

if (isset($_SERVER['REMOTE_ADDR'])) {
  $settings['reverse_proxy'] = TRUE;
  $settings['reverse_proxy_addresses'] = array($_SERVER['REMOTE_ADDR']);
}

# Private filesystem
# @todo: create private file dir for all environments.
$settings['file_private_path'] = '/var/www/fsa/files-private';

if(!empty($_SERVER['SERVER_ADDR'])){
  // This should return last section of IP, such as "198". (dont want/need to expose more info).
  //drupal_add_http_header('X-Webserver', end(explode('.', $_SERVER['SERVER_ADDR'])));
  $pcs = explode('.', $_SERVER['SERVER_ADDR']);
  header('X-Webserver: '. end($pcs));
}

// Disallow configuration changes by default.
$settings['config_readonly'] = TRUE;

// The config names that are allowed to be changed in readonly environments.
$settings['config_readonly_whitelist_patterns'] = [
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

// Memcache.
// $settings['memcache']['servers'] = ['127.0.0.1:11211' => 'default'];
// if (class_exists('Memcached')) {
//   /**
//    * Memcache configuration.
//    */
//   $settings['memcache']['extension'] = 'Memcached';
//   $settings['memcache']['bins'] = ['default' => 'default'];
//   $settings['memcache']['key_prefix'] = 'fsa_' . $env;
//   $settings['cache']['default'] = 'cache.backend.memcache';
//   $settings['cache']['bins']['render'] = 'cache.backend.memcache';
//   $settings['cache']['bins']['dynamic_page_cache'] = 'cache.backend.memcache';
//   $settings['cache']['bins']['bootstrap'] = 'cache.backend.memcache';
//   $settings['cache']['bins']['config'] = 'cache.backend.memcache';
//   $settings['cache']['bins']['discovery'] = 'cache.backend.memcache';
//   // Enable stampede protection.
//   $settings['memcache']['stampede_protection'] = TRUE;
//   // High performance - no hook_boot(), no hook_exit(), ignores Drupal IP
//   // blacklists.
//   $conf['page_cache_invoke_hooks'] = FALSE;
//   $conf['page_cache_without_database'] = TRUE;
//   // Memcached PECL Extension Support.
//   // Adds Memcache binary protocol and no-delay features (experimental).
//   $settings['memcache']['options'] = [
//     \Memcached::OPT_COMPRESSION => FALSE,
//     \Memcached::OPT_DISTRIBUTION => \Memcached::DISTRIBUTION_CONSISTENT,
//     \Memcached::OPT_BINARY_PROTOCOL => TRUE,
//     \Memcached::OPT_TCP_NODELAY => TRUE,
//   ];
// }

switch ($env) {
  case 'prod':
    $settings['simple_environment_indicator'] = '#d4000f Production';
    $settings['file_private_path'] = '/var/www/fsa.prod.wunder.io/private-files';

    // GTM Environment overrides.
    $config['google_tag.settings']['environment_id'] = 'env-2';
    $config['google_tag.settings']['environment_token'] = 'qiSnyllzn5flpcJTEzjYGA';

    $config['elasticsearch_helper.settings']['elasticsearch_helper']['host'] = '10.2.3.85';

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

    // Memcache servers.
    $settings['memcache']['servers'] = array(
      '10.2.5.163:11211' => 'default',
      '10.2.3.18:11211' => 'default'
    );

    break;

  case 'develop':
    $settings['simple_environment_indicator'] = '#004984 Development';

    // GTM Environment overrides.
    $config['google_tag.settings']['environment_id'] = 'env-6';
    $config['google_tag.settings']['environment_token'] = '4d3H88TmNOCwXVDx0PK8bg';

    break;

  case 'stage':
    $settings['simple_environment_indicator'] = '#e56716 Stage';

    // GTM Environment overrides.
    $config['google_tag.settings']['environment_id'] = 'env-5';
    $config['google_tag.settings']['environment_token'] = 'nNEwJ_lItnO48_pabdUErg';

    break;

  case 'local':
    $settings['simple_environment_indicator'] = '#88b700 Local';

    $config['config_split.config_split.dev']['status'] = TRUE;
    $settings['config_readonly'] = FALSE;

    // GTM Environment (below values should be as default configurations).
    $config['google_tag.settings']['environment_id'] = 'env-7';
    $config['google_tag.settings']['environment_token'] = 'a4fGxt3oZ4lNeD1SjVDqdA';

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
  'fsa\.dev\.wunder\.io$',
  'fsa\.stage\.wunder\.io$',
  'fsa\.prod\.wunder\.io$',
  'foodgovuk\.*\.acquia-sites\.com',
];

/**
 * Access control for update.php script.
 */
$settings['update_free_access'] = FALSE;

/**
 * Load services definition file.
 */
$settings['container_yamls'][] = __DIR__ . '/services.yml';

/**
 * Environment specific override configuration, if available.
 */
if (file_exists(__DIR__ . '/settings.local.php')) {
   include __DIR__ . '/settings.local.php';
}
$settings['install_profile'] = 'standard';

// Automatically generated include for settings managed by ddev.
if (file_exists($app_root . '/' . $site_path . '/settings.ddev.php')) {
  include $app_root . '/' . $site_path . '/settings.ddev.php';
}
