<?php
//if (isset($_SERVER['HTTP_CF_CONNECTING_IP'])) {
//  $_SERVER['REMOTE_ADDR'] = $_SERVER['HTTP_CF_CONNECTING_IP'];
//}

if (SETTINGS_ENVIRONMENT === D_ENVIRONMENT_TEST || SETTINGS_ENVIRONMENT === D_ENVIRONMENT_PROD) {
  $config['cloudflare.settings']['apikey'] = '2545f28b63410117ecd9f9fdc5f1718ee8554';
  $config['cloudflare.settings']['email'] = 'FSA.IT@food.gov.uk';
  $config['cloudflare.settings']['valid_credentials'] = TRUE;
  $config['cloudflare.settings']['bypass_host'] = '';
  $config['cloudflare.settings']['client_ip_restore_enabled'] = FALSE;
  $config['cloudflare.settings']['zone_id'] = '8a5edb81ac555271898107df25f778b0';
}