<?php

/**
 * @file
 * Geolocation module settings.
 */

if (SETTINGS_PLATFORM === D_PLATFORM_ACQUIA) {
  $config['geolocation.settings']['google_map_api_key'] = getenv('GOOGLE_MAPS_API_KEY');
}
elseif (SETTINGS_PLATFORM === D_PLATFORM_DOCKER) {
  $config['geolocation.settings']['google_map_api_key'] = 'AIzaSyBCSLNcp6RDWYKZam1gqbd6zw4V9lg2T60';
}