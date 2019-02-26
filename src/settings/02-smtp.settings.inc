<?php

/**
 * @file
 * SMTP module settings.
 */

// SMTP settings: from environment variables.
$config['smtp.settings']['smtp_host']     = getenv('SMTP_HOST');
$config['smtp.settings']['smtp_port']     = getenv('SMTP_PORT');
$config['smtp.settings']['smtp_username'] = getenv('SMTP_USERNAME');
$config['smtp.settings']['smtp_password'] = getenv('SMTP_PASSWORD');
$config['smtp.settings']['smtp_from']     = getenv('SMTP_FROM');

$config['mailsystem.settings']['defaults']['sender'] = 'SMTPMailSystem';
$config['mailsystem.settings']['modules']['content_moderation_notifications']['none']['sender'] = 'SMTPMailSystem';
$config['mailsystem.settings']['modules']['webform']['none']['sender'] = 'SMTPMailSystem';

if (empty($config['smtp.settings']['smtp_host'])) {
  // If we can't work out the SMTP server then don't send any emails.
  $config['smtp.settings']['smtp_on'] = FALSE;
}

if (SETTINGS_ENVIRONMENT === D_ENVIRONMENT_LOCAL) {
  $config['smtp.settings']['smtp_protocol'] = 'standard';
}
