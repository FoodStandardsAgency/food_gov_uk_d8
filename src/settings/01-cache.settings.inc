<?php

/**
 * @file
 * Caching settings.
 */


if (class_exists('Memcached')) {
  /**
   * Memcache configuration.
   */
  $settings['memcache']['extension'] = 'Memcached';
  $settings['memcache']['bins'] = ['default' => 'default'];
  $settings['memcache']['key_prefix'] = 'fsa_' . SETTINGS_ENVIRONMENT;
  $settings['cache']['default'] = 'cache.backend.memcache';
  $settings['cache']['bins']['render'] = 'cache.backend.memcache';
  $settings['cache']['bins']['dynamic_page_cache'] = 'cache.backend.memcache';
  $settings['cache']['bins']['bootstrap'] = 'cache.backend.memcache';
  $settings['cache']['bins']['config'] = 'cache.backend.memcache';
  $settings['cache']['bins']['discovery'] = 'cache.backend.memcache';
  // Enable stampede protection.
  $settings['memcache']['stampede_protection'] = TRUE;
  // High performance - no hook_boot(), no hook_exit(), ignores Drupal IP
  // blacklists.
  $conf['page_cache_invoke_hooks'] = FALSE;
  $conf['page_cache_without_database'] = TRUE;
  // Memcached PECL Extension Support.
  // Adds Memcache binary protocol and no-delay features (experimental).
  $settings['memcache']['options'] = [
    \Memcached::OPT_COMPRESSION => FALSE,
    \Memcached::OPT_DISTRIBUTION => \Memcached::DISTRIBUTION_CONSISTENT,
    \Memcached::OPT_BINARY_PROTOCOL => TRUE,
    \Memcached::OPT_TCP_NODELAY => TRUE,
  ];

  if (SETTINGS_PLATFORM === D_PLATFORM_ACQUIA) {
    $settings['memcache']['servers'] = ['127.0.0.1:11211' => 'default'];
  }
}

if (in_array(SETTINGS_ENVIRONMENT, [D_ENVIRONMENT_TEST, D_ENVIRONMENT_PROD])) {
  $config['system.performance']['css']['gzip'] = TRUE;
  $config['system.performance']['css']['preprocess'] = TRUE;

  $config['system.performance']['js']['gzip'] = TRUE;
  $config['system.performance']['js']['preprocess'] = TRUE;
}
