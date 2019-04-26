<?php

/**
 * @file
 * webform module settings.
 */

if (SETTINGS_PLATFORM === D_PLATFORM_ACQUIA) {
  $config['webform.settings']['export']['temp_directory'] = "/mnt/gfs/{$_ENV['AH_SITE_GROUP']}.{$_ENV['AH_SITE_ENVIRONMENT']}/webform-tmp";
}
