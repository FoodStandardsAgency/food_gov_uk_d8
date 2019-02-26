<?php

/**
 * @file
 * Settings for the shield module.
 */

// Shield config.
$config['shield.settings']['credentials']['shield']['user'] = getenv('HTTP_AUTH_USER');
$config['shield.settings']['credentials']['shield']['pass'] = getenv('HTTP_AUTH_PWD');

if (SETTINGS_ENVIRONMENT === D_ENVIRONMENT_PROD) {
  $config['shield.settings']['credentials']['shield']['user'] = NULL;
}
elseif (SETTINGS_ENVIRONMENT === D_ENVIRONMENT_LOCAL) {
  $config['shield.settings']['credentials']['shield']['user'] = NULL;
}
