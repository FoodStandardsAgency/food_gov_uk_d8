<?php

/**
 * @file
 * Settings for the Stage File proxy module.
 */

if (SETTINGS_ENVIRONMENT !== D_ENVIRONMENT_PROD) {
  $config['stage_file_proxy.settings']['origin'] = 'https://www.food.gov.uk';
}
