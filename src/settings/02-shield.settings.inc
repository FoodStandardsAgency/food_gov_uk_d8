<?php

/**
 * @file
 * Settings for the shield module.
 */

if (SETTINGS_ENVIRONMENT === D_ENVIRONMENT_PROD) {
  $config['shield.settings']['credentials']['shield']['user'] = NULL;
}
elseif (SETTINGS_ENVIRONMENT === D_ENVIRONMENT_LOCAL) {
  $config['shield.settings']['credentials']['shield']['user'] = NULL;
}
