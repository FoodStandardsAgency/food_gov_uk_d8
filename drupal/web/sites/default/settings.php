<?php

/**
 * General settings.php for all environments.
 * You could use this to add general settings to be used for all environments.
 */


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
  'You have just used your one-time login link. It is no longer necessary to use this link to log in. Please change your password.' => 'You have just used your one-time login link. Please set yourself a <a href="/profile/manage/password">password</a> that you can use to log in again.',
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

// Define specific admin pages to allow configuration changes on production.
// @todo: follow issue https://www.drupal.org/node/2826274 for a fix on this.
$config_allowed = [
  '/admin/structure/menu/manage/account',
  '/admin/structure/menu/manage/main',
  '/admin/structure/menu/manage/help',
  '/admin/structure/menu/manage/footer',
  '/admin/config/system/site-information',
  '/admin/config/fsa/ratings',
  '/admin/config/fsa/ratings/translate/cy/add',
  '/admin/config/fsa/ratings/translate/cy/edit',
  '/admin/config/fsa/consultations',
  '/admin/config/fsa/consultations/translate/cy/add',
  '/admin/config/fsa/consultations/translate/cy/edit',
  '/admin/config/content/embed/button/manage/document',
  '/admin/config/content/embed/button/manage/media_entity_embed',
  '/admin/config/content/embed/button/manage/image',
  '/admin/config/content/embed/button/manage/node',
];

// Allow config changes on specified path pattern and command line.
if (in_array($_SERVER['REQUEST_URI'], $config_allowed) || PHP_SAPI === 'cli') {
  $settings['config_readonly'] = FALSE;
}

// We want to sometimes manage webforms on staging, temporarily allow config
// changes here.
/*
if (strpos($_SERVER['REQUEST_URI'], '/admin/structure/webform/manage') === 0) {
  $settings['config_readonly'] = FALSE;
}
*/

// Be sure to have config_split.dev disabled by default.
$config['config_split.config_split.dev']['status'] = FALSE;

$config['elasticsearch_helper.settings']['elasticsearch_helper']['host'] = getenv('DB_HOST_DRUPAL');

$env = getenv('WKV_SITE_ENV');
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
  CONFIG_SYNC_DIRECTORY => '../sync',
);

/**
 * Trusted hosts patterns.
 */
$settings['trusted_host_patterns'] = [
  'food\.gov\.uk$',
  'fsa\.dev\.wunder\.io$',
  'fsa\.stage\.wunder\.io$',
  'fsa\.prod\.wunder\.io$',
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
