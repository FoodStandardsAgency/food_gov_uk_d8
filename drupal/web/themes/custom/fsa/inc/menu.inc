<?php

/**
 * @file
 * Includes FSA theme menu preprocess functionality.
 */

/**
 * Implements template_preprocess_menu().
 */
function fsa_preprocess_menu(&$variables) {
  if ($variables['menu_name'] == 'footer') {
    if (isset($variables['items']['contact.site_page'])) {
      $variables['items']['contact.site_page']['title'] = t('Feedback');
    }
  }
}
