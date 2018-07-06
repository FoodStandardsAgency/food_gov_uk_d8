<?php

namespace Drupal\fsa_notify;

use Alphagov\Notifications\Exception\ApiException;
use Alphagov\Notifications\Exception\NotifyException;
use Drupal\Core\Url;
use Drupal\user\Entity\User;
use Drupal\Component\Utility\UrlHelper;

/**
 * FSA Notify Emailing class.
 *
 * @see // https://github.com/alphagov/notifications-php-client
 */
class FsaNotifyAPIemail extends FsaNotifyAPI {

  /**
   * {@inheritdoc}
   */
  public function __construct() {
    $state_key = "fsa_notify.template_email";
    $state_key_cy = "fsa_notify.template_email_cy";
    parent::__construct($state_key, $state_key_cy);
  }

  /**
   * {@inheritdoc}
   */
  public function send(User $user, string $reference, array $personalisation) {

    $user_lang = $user->getPreferredLangcode();
    $base_url = FsaNotifyMessage::baseUrl();

    if ($user_lang == 'cy') {
      $template_id = $this->templateIdCy;
      $base_url .= '/cy';
    }
    else {
      $template_id = $this->templateId;
    }

    $email = $user->getEmail();

    // Get email subject based on if immediate or digest emails.
    switch ($reference) {
      case 'immediate':
        $subject = t('FSA Update: @title', ['@title' => $personalisation['subject']], ['langcode' => $user_lang])->render();
        break;

      case 'daily':
        $subject = t('FSA daily digest update', ['langcode' => $user_lang])->render();
        break;

      case 'weekly':
        $subject = t('FSA weekly digest update', ['langcode' => $user_lang])->render();
        break;

      default:
        $subject = t('FSA Update', [], ['langcode' => $user_lang])->render();
    }
    $personalisation['subject'] = $subject;

    // Craft the login url.
    $personalisation['login'] = $base_url . Url::fromRoute('fsa_signin.default_controller_signInPage', [])->toString();

    // Craft the unsubscribe url and add user parameters.
    $unsubscribe = $base_url . Url::fromRoute('fsa_signin.default_controller_unsubscribe', [])->toString();
    $personalisation['unsubscribe'] = $unsubscribe . '?' . UrlHelper::buildQuery(['id' => $user->id(), 'email' => $email]);

    // Debugging mode, just log the Notify template variables.
    if (\Drupal::state()->get('fsa_notify.collect_send_log_only')) {
      \Drupal::logger('fsa_notify')->debug('Notify email: <ul><li>To: %email</li><li>template_id %template_id</li><li>personalization: <pre>%personalization</pre></li><li>reference: %reference</li></ul>', [
        '%email' => $email,
        '%template_id' => $template_id,
        '%personalization' => print_r($personalisation, 1),
        '%reference' => $reference,
      ]);

      return FALSE;
    }

    $msg = sprintf('Notify API: sendEmail(%s)', $email);
    try {
      $this->api->sendEmail(
        $email,
        $template_id,
        $personalisation,
        $reference
      );
    }
    catch (ApiException $e) {
      // Get response body (the actual error message).
      $msg .= (string) $e->getResponse()->getBody();
      $this->logAndException($msg, $e);
    }
    catch (NotifyException $e) {
      $this->logAndException($msg, $e);
    }
    catch (Exception $e) {
      $this->logAndException($msg, $e);
    }
  }

}
