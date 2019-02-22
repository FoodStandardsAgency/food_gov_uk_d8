<?php

/**
 * @file
 * Acquia settings file.
 */

/**
 * On Acquia Cloud, this include file configures Drupal to use the correct
 * database in each site environment (Dev, Stage, or Prod). To use this
 * settings.php for development on your local workstation, set $db_url
 * (Drupal 5 or 6) or $databases (Drupal 7 or 8) as described in comments above.
 */
if (file_exists('/var/www/site-php')) {
  require '/var/www/site-php/foodgovuk/foodgovuk-settings.inc';
}


if (SETTINGS_ENVIRONMENT === D_ENVIRONMENT_LOCAL) {
  // Disable Acquia module config.
  $config['acquia_connector.settings']['subscription_data']['active'] = FALSE;
  $config['acquia_connector.settings']['subscription_data']['href'] = NULL;
  $config['acquia_connector.settings']['subscription_data']['uuid'] = NULL;
  $config['purge.plugins']['purgers'] = [];
}
