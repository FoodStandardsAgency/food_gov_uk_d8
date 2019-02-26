<?php

/**
 * @file
 * Warden configuration
 */

if (SETTINGS_ENVIRONMENT === D_ENVIRONMENT_PROD) {
  $config['warden.settings']['warden_allow_requests'] = TRUE;
  $config['warden.settings']['warden_public_allow_ips'] = '127.0.0.1,2001:4800:7811:513:59b9:beb2:ff04:b7a1,52.50.239.9';
  $config['warden.settings']['warden_server_host_path'] = 'https://warden.deeson.net';
  $config['warden.settings']['warden_match_library'] = TRUE;
  $config['warden.settings']['warden_preg_match_custom'] = '{^modules\/custom\/*}';
  $config['warden.settings']['warden_preg_match_contrib'] = '{^modules\/contrib\/*}';
  $config['warden.settings']['warden_list_libraries'] = [
    'server' => array(
      'php' => PHP_VERSION,
    ),
  ];
}
