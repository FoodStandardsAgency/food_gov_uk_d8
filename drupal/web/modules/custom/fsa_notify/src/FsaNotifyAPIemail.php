<?php

// https://github.com/alphagov/notifications-php-client

namespace Drupal\fsa_notify;

use Alphagov\Notifications\Exception\ApiException;
use Alphagov\Notifications\Exception\NotifyException;
use Drupal\fsa_notify\FsaNotifyAPI;
use Drupal\user\Entity\User;

class FsaNotifyAPIemail extends FsaNotifyAPI {

  public function __construct() {
    $state_key = "fsa_notify.template_email";
    parent::__construct($state_key);
  }
  
  public function send(User $user, string $reference, array $personalisation) {
  
    $email = $user->getEmail();

    try {
      $msg = sprintf('Notify API: sendEmail(%s)', $email);
      $this->api->sendEmail(
        $email,
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
