<?php

/**
 * @file
 * Detect the current system environment.
 */

const D_ENVIRONMENT_LOCAL = 'local';
const D_ENVIRONMENT_DEV = 'dev';
const D_ENVIRONMENT_TEST = 'test';
const D_ENVIRONMENT_PROD = 'prod';

const D_PLATFORM_DOCKER = 'docker';
const D_PLATFORM_PIPELINES = 'pipelines';
const D_PLATFORM_ACQUIA = 'acquia';
const D_PLATFORM_PANTHEON = 'pantheon';
const D_PLATFORM_PLATFORMSH = 'platformsh';

/**
 * Map domains to environment types here.
 */
$base_domains = [
  getenv('D4D_HOSTNAME') => 'local',
  'docker.local' => 'local',
  'www.food.gov.uk' => 'prod',
];

$platform = '';
$env = '';
$instance = $_SERVER['HTTP_HOST'];

if (isset($_ENV['AH_SITE_ENVIRONMENT'])) {
  $platform = D_PLATFORM_ACQUIA;

  if (!empty($_ENV['AH_PRODUCTION'])) {
    $env = D_ENVIRONMENT_PROD;
  }
  elseif ($_ENV['AH_SITE_ENVIRONMENT'] === D_ENVIRONMENT_DEV) {
    $env = D_ENVIRONMENT_DEV;
  }
  else {
    $env = D_ENVIRONMENT_TEST;
  }
}
elseif (getenv('DOCKER_LOCAL')) {
  // Local environment settings - see 90-docker-settings.inc.
  $platform = D_PLATFORM_DOCKER;
  $env = $base_domains[getenv('D4D_HOSTNAME')];
  $instance = getenv('D4D_HOSTNAME');
}
else {
  // Running under Pipelines.
  $platform = D_PLATFORM_PIPELINES;
  $env = D_ENVIRONMENT_LOCAL;
  $instance = 'localhost';
}

if (!empty($env) && !empty($platform) && !empty($instance)) {
  define('SETTINGS_PLATFORM', $platform);
  define('SETTINGS_ENVIRONMENT', $env);
  define('SETTINGS_INSTANCE', $instance);
}
