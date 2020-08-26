<?php

/**
 * @file
 * Caching settings.
 */


if (class_exists('Memcached')) {

  // Memcache.
  $settings['cache']['default'] = 'cache.backend.memcache';

  // Acquia Memcache settings.
  if (SETTINGS_ENVIRONMENT === D_PLATFORM_ACQUIA && file_exists($app_root . '/' . $site_path . '/cloud-memcache-d8.php')) {
    require $app_root . '/' . $site_path . '/cloud-memcache-d8.php';
  }

  if (SETTINGS_ENVIRONMENT === D_ENVIRONMENT_LOCAL) {
    $settings['memcache']['servers'] = ['memcached:11211' => 'default'];
  }

}

if (in_array(SETTINGS_ENVIRONMENT, [D_ENVIRONMENT_TEST, D_ENVIRONMENT_PROD])) {
  $config['system.performance']['css']['gzip'] = TRUE;
  $config['system.performance']['css']['preprocess'] = TRUE;

  $config['system.performance']['js']['gzip'] = TRUE;
  $config['system.performance']['js']['preprocess'] = TRUE;
}

// Changing this value forces APC to invalidate all PHP code cache on deployment.
// This ensures that no fatal errors occur on deployment when classes move or
// are renamed. Using the release tag here for transparency.
$settings['deployment_identifier'] = '1.19.0-rc1';
