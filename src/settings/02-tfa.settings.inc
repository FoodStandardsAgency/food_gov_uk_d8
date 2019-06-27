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

if(SETTINGS_ENVIRONMENT == D_ENVIRONMENT_PROD) {
  #This is the wrapping key which works with the prod pair.pem certificate. It's the same as the dev wrapping key, but it doesn't need to be.
  $config['lockr.secret_info']['fsa_2fa_key_2019']['wrapping_key'] = '$2$iLmFAI8cwpW7NMlXrr3dunRJHFCXVvyKjdK5gGNvBVw=';
} else {
  #This is the wrapping key which works with the dev pair.pem certificate
  $config['lockr.secret_info']['fsa_2fa_key_2019']['wrapping_key'] = '$2$iLmFAI8cwpW7NMlXrr3dunRJHFCXVvyKjdK5gGNvBVw=';
}
