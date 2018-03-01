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

    try {
      $msg = sprintf('Notify API: sendSms(%s)', $phoneNumber);
      $this->api->sendSms(
        $phoneNumber,
        $this->template_id,
        $personalisation,
        $reference
      );
    }
    catch (ApiException $e) {
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
