<?php

/**
 * @file
 * Google Tag Manager settings.
 */

if (SETTINGS_PLATFORM === D_PLATFORM_ACQUIA) {
  $config['google_tag.settings']['environment_id'] = getenv('GOOGLE_TAG_ENV_ID');
  $config['google_tag.settings']['environment_token'] = getenv('GOOGLE_TAG_ENV_TOKEN');

  // FSA Data Studio Measurement Protocol: switch UA- code to correct ID.
  $config['fsa_gds_mp']['ga_tracking_id'] = getenv('GA_TRACKING_ID');
}
elseif (SETTINGS_PLATFORM === D_PLATFORM_DOCKER) {
  $config['google_tag.settings']['environment_id'] = 'env-7';
  $config['google_tag.settings']['environment_token'] = 'a4fGxt3oZ4lNeD1SjVDqdA';
  $config['fsa_gds_mp']['ga_tracking_id'] = 'UA-54078849-3';
}
