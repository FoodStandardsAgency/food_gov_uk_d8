<?php

/**
 * General settings.php for all environments.
 * You could use this to add general settings to be used for all environments.
 */

/**
 * Load services definition file - can be overridden below.
 */
$settings['container_yamls'][] = __DIR__ . '/services.yml';

if (SETTINGS_PLATFORM === D_PLATFORM_ACQUIA) {
  $env_services = $app_root . '/' . $site_path . '/' . SETTINGS_ENVIRONMENT . '.services.yml';

  if (!file_exists($env_services)) {
    die('Could not find Acquia environment services file ' . $env_services);
  }

  $settings['container_yamls'][] = $env_services;
}

$settings['hash_salt'] = 'B081u6MDeLm3bRi5niieR-797DOulNMA-SGCoprrcy5Gjn-hDNAkiy1k8Pnb9y8n1zSXWu4aQQ';

if ( (isset($_SERVER["HTTPS"]) && strtolower($_SERVER["HTTPS"]) == "on")
  || (isset($_SERVER["HTTP_X_FORWARDED_PROTO"]) && $_SERVER["HTTP_X_FORWARDED_PROTO"] == "https")
  || (isset($_SERVER["HTTP_HTTPS"]) && $_SERVER["HTTP_HTTPS"] == "on")
) {
  $_SERVER["HTTPS"] = "on";

  // Tell Drupal we're using HTTPS (url() for one depends on this).
  $settings['https'] = TRUE;
}

if (SETTINGS_PLATFORM === D_PLATFORM_ACQUIA && isset($_SERVER['REMOTE_ADDR'])) {
  $settings['reverse_proxy'] = TRUE;
  $settings['reverse_proxy_addresses'] = array($_SERVER['REMOTE_ADDR']);
}

# Private filesystem
if (SETTINGS_PLATFORM === D_PLATFORM_ACQUIA) {
  $settings['file_private_path'] = '/mnt/files/' . $_ENV['AH_SITE_GROUP'] . '.' . $_ENV['AH_SITE_ENVIRONMENT'] . '/' . $site_path . '/files-private';
  $config['system.file']['path']['temporary'] = '/tmp'; //"/mnt/gfs/{$_ENV['AH_SITE_GROUP']}.{$_ENV['AH_SITE_ENVIRONMENT']}/tmp";
}

if (!empty($_SERVER['SERVER_ADDR'])){
  // This should return last section of IP, such as "198". (dont want/need to expose more info).
  //drupal_add_http_header('X-Webserver', end(explode('.', $_SERVER['SERVER_ADDR'])));
  $pcs = explode('.', $_SERVER['SERVER_ADDR']);
  header('X-Webserver: '. end($pcs));
}

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
  'key.*',
  'encrypt.*',
  'tfa.*',
  'webform.webform.food_fraud_crime',
  'webform.webform.food_poisoning',
  'webform.webform.foreign_object',
  'webform.webform.poor_hygiene_practices',
];

/**
 * Trusted hosts patterns.
 */
$settings['trusted_host_patterns'] = [
  'food\.gov\.uk$',
  'fsa\.localhost$',
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

/**
 * Install profile.
 */
$settings['install_profile'] = 'standard';
