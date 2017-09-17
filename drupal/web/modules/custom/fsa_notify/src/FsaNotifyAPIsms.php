<?php

// https://github.com/alphagov/notifications-php-client

namespace Drupal\fsa_notify;

use Alphagov\Notifications\Exception\ApiException;
use Alphagov\Notifications\Exception\NotifyException;
use Drupal\fsa_notify\FsaNotifyAPI;
use Drupal\user\Entity\User;

class FsaNotifyAPIsms extends FsaNotifyAPI {

  public function __construct() {
    $state_key = "fsa_notify.template_sms";
    parent::__construct($state_key);
  }
  
  public function send(User $user, string $reference, array $personalisation) {

    $phoneNumber = $user->field_notification_sms->getString();
    // Phone number is optional field!
    if (empty($phoneNumber)) {
      return;
    }

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
