<?php

/**
 * @file
 * TFA module settings.
 */

if (SETTINGS_ENVIRONMENT === D_ENVIRONMENT_PROD || SETTINGS_ENVIRONMENT === D_ENVIRONMENT_TEST) {
  $config['tfa.settings']['enabled'] = TRUE;
}
else {
  $config['tfa.settings']['enabled'] = FALSE;
}
