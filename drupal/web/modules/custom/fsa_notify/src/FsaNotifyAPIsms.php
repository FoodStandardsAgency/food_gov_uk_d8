<?php

namespace Drupal\fsa_notify;

use Alphagov\Notifications\Exception\ApiException;
use Alphagov\Notifications\Exception\NotifyException;
use Drupal\fsa_signin\SignInService;
use Drupal\user\Entity\User;

/**
 * Notify SMS sending class.
 *
 * @see https://github.com/alphagov/notifications-php-client
 */
class FsaNotifyAPIsms extends FsaNotifyAPI {

  /**
   * {@inheritdoc}
   */
  public function __construct() {
    $state_key = "fsa_notify.template_sms";
    parent::__construct($state_key);
  }

  /**
   * {@inheritdoc}
   */
  public function send(User $user, string $reference, array $personalisation) {

    $phoneNumber = $user->field_notification_sms->getString();
    // Phone number is optional field!
    if (empty($phoneNumber)) {
      return;
    }

    // Add the country code if missing.
    if (substr($phoneNumber, '0', '2') != SignInService::DEFAULT_COUNTRY_CODE) {
      $phoneNumber = SignInService::DEFAULT_COUNTRY_CODE . $phoneNumber;
    }

    // And the plus is required by notify.
    $phoneNumber = '+' . $phoneNumber;

    // Debugging mode, just log the Notify template variables.
    if (\Drupal::state()->get('fsa_notify.collect_send_log_only')) {
      \Drupal::logger('fsa_notify')->debug('Notify SMS: <ul><li>To: %phoneNumber</li><li>template_id %template_id</li><li>personalization: <pre>%personalization</pre></li><li>reference: %reference</li></ul>', [
        '%phoneNumber' => $phoneNumber,
        '%template_id' => $this->templateId,
        '%personalization' => print_r($personalisation, 1),
        '%reference' => $reference,
      ]);

      return FALSE;
    }

    $msg = sprintf('Notify API: sendSms(%s)', $phoneNumber);
    try {
      $this->api->sendSms(
        $phoneNumber,
        $this->templateId,
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
