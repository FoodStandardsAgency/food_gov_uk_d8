<?php

/**
 * @file
 * Settings for the simple environment indicator module.
 */

$environment_values = [
  'prod' =>  '#d4000f Production',
  'test' => '#e56716 Stage',
  'dev' => '#004984 Development',
  'local' => '#88b700 Local',
];

if (!empty($environment_values[SETTINGS_ENVIRONMENT])) {
  $settings['simple_environment_indicator'] = $environment_values[SETTINGS_ENVIRONMENT];
}
