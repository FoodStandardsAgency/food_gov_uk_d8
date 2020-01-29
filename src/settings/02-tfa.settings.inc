<?php

/**
 * @file
 * TFA module settings.
 */

// Enabled on Staging and Prod.
if (SETTINGS_ENVIRONMENT === D_ENVIRONMENT_PROD) {
  $config['tfa.settings']['enabled'] = TRUE;
}
else {
  $config['tfa.settings']['enabled'] = FALSE;
}

// Select correct certificate location and wrapping key.
if (SETTINGS_ENVIRONMENT == D_ENVIRONMENT_PROD) {
  //The prod_wrapping_key must be used with the prod pair.pem
  $config['lockr.secret_info']['fsa_2fa_key_2019']['wrapping_key'] = '$2' . getenv('TFA_WRAPPING_KEY');
  $config['lockr.settings']['cert_path'] = '/mnt/gfs/foodgovuk/sites/default/files-private/lockr/prod/pair.pem';
} 
else if (SETTINGS_PLATFORM === D_PLATFORM_ACQUIA) {
  //The dev_wrapping_key must be used with the prod pair.pem
  $config['lockr.secret_info']['fsa_2fa_key_2019']['wrapping_key'] = '$2' . getenv('TFA_WRAPPING_KEY');
  $config['lockr.settings']['cert_path'] = '/mnt/gfs/foodgovuk.' . $_ENV['AH_SITE_ENVIRONMENT'] . '/sites/default/files-private/lockr/dev/pair.pem';
}
