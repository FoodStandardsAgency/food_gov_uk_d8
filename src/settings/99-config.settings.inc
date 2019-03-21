<?php

/**
 * @file
 * Configuration management overrides.
 *
 * @see http://geertvd.github.io/post/exclude-config-from-cmi-in-d8/
 */

/**
 * Location of the site configuration files.
 */
$config_directories = array(
  CONFIG_SYNC_DIRECTORY => dirname(DRUPAL_ROOT) . '/config/default',
);

// Be sure to have config_split.dev disabled by default.
$config['config_split.config_split.dev']['status'] = FALSE;

// Disallow configuration changes by default.
$settings['config_readonly'] = TRUE;

// Allow configuration changes via drush (command line).
if (PHP_SAPI === 'cli' || SETTINGS_ENVIRONMENT === 'test') {
  $settings['config_readonly'] = FALSE;
}

/**
 * Local env can make these changes.
 */
if (SETTINGS_ENVIRONMENT === D_ENVIRONMENT_LOCAL) {
  $config['config_split.config_split.dev']['status'] = TRUE;
  $settings['config_readonly'] = FALSE;
}
