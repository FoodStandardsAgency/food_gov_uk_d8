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
