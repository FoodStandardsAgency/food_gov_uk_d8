<?php
//if (isset($_SERVER['HTTP_CF_CONNECTING_IP'])) {
//  $_SERVER['REMOTE_ADDR'] = $_SERVER['HTTP_CF_CONNECTING_IP'];
//}

if (SETTINGS_ENVIRONMENT === D_ENVIRONMENT_TEST || SETTINGS_ENVIRONMENT === D_ENVIRONMENT_PROD) {
  $config['cloudflare.settings']['apikey'] = getenv('CF_API_KEY');
  $config['cloudflare.settings']['email'] = getenv('CF_EMAIL');
  $config['cloudflare.settings']['valid_credentials'] = TRUE;
  $config['cloudflare.settings']['bypass_host'] = '';
  $config['cloudflare.settings']['client_ip_restore_enabled'] = FALSE;
  $config['cloudflare.settings']['zone_id'] = getenv('CF_ZONE_ID');
}