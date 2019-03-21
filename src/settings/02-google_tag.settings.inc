<?php

/**
 * @file
 * Google Tag Manager settings.
 */

if (SETTINGS_ENVIRONMENT === D_ENVIRONMENT_PROD) {
  $config['google_tag.settings']['environment_id'] = 'env-2';
  $config['google_tag.settings']['environment_token'] = 'qiSnyllzn5flpcJTEzjYGA';

  // FSA Data Studio Measurement Protocol: switch UA- code to correct ID.
  $config['fsa_gds_mp']['ga_tracking_id'] = 'UA-54078849-1';
}
elseif (SETTINGS_ENVIRONMENT === D_ENVIRONMENT_TEST) {
  // GTM Environment overrides.
  $config['google_tag.settings']['environment_id'] = 'env-5';
  $config['google_tag.settings']['environment_token'] = 'nNEwJ_lItnO48_pabdUErg';
  $config['fsa_gds_mp']['ga_tracking_id'] = 'UA-54078849-3';
}
elseif (SETTINGS_ENVIRONMENT === D_ENVIRONMENT_DEV) {
  $config['google_tag.settings']['environment_id'] = 'env-6';
  $config['google_tag.settings']['environment_token'] = '4d3H88TmNOCwXVDx0PK8bg';
  $config['fsa_gds_mp']['ga_tracking_id'] = 'UA-54078849-3';
}
elseif (SETTINGS_ENVIRONMENT === D_ENVIRONMENT_LOCAL) {
  $config['google_tag.settings']['environment_id'] = 'env-7';
  $config['google_tag.settings']['environment_token'] = 'a4fGxt3oZ4lNeD1SjVDqdA';
  $config['fsa_gds_mp']['ga_tracking_id'] = 'UA-54078849-3';
}
