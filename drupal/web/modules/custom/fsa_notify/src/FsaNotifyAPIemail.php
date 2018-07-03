<?php

namespace Drupal\fsa_notify;

use Alphagov\Notifications\Exception\ApiException;
use Alphagov\Notifications\Exception\NotifyException;
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

    if ($user->getPreferredLangcode() == 'cy') {
      $template_id = $this->templateIdCy;
    }
    else {
      $template_id = $this->templateId;
    }

    $email = $user->getEmail();

    // Add the user parameters to the unsubscribe URL.
    $personalisation['unsubscribe'] = $personalisation['unsubscribe'] . '?' . UrlHelper::buildQuery(['id' => $user->id(), 'email' => $email]);

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
