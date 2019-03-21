<?php

/**
 * Settings for dompdf, part of entity print
 */

if (SETTINGS_ENVIRONMENT === D_ENVIRONMENT_LOCAL) {
  $config['entity_print.print_engine.dompdf']['settings']['verify_name'] = FALSE;
  $config['entity_print.print_engine.dompdf']['settings']['verify_peer'] = FALSE;
}
