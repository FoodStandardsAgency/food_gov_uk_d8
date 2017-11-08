<?php

/**
 * @file
 * Includes FSA theme status messages preprocess functionality.
 */

/**
 * Implements template_preprocess_status_messages().
 */
function fsa_preprocess_status_messages(&$variables) {

  // Unset webform preview warning message.
  if (isset($variables['message_list']['warning'][0])) {
    $warning_message = $variables['message_list']['warning'][0]->__toString();
    if ($warning_message == 'Here are the details you are about to report.') {
      $variables['message_list'] = [];
    }
  }
}
