<?php

namespace Drupal\fsa_notify;

use Drupal\Core\Url;
use Drupal\user\Entity\User;

class FsaNotifyAPI {

  private $api = NULL;
  private $email_template_id = NULL;
  private $sms_template_id = NULL;

  public function __construct() {
  
    $state_key = 'fsa_notify.api';
    $api_key = \Drupal::state()->get($state_key);
    if (empty($api_key)) {
      $msg = sprintf('Notify API key not specified in state "%s".', $state_key);
      $this->logAndException($msg);
    }
  
    try {
      $this->api = new \Alphagov\Notifications\Client([
        'apiKey' => $api_key,
        'httpClient' => new \Http\Adapter\Guzzle6\Client,
      ]);
    }
    catch (Exception $e) {
      $this->api = NULL;
      $msg = $e->getMessage();
      if (!is_string($msg)) {
        $msg = print_r($msg, TRUE);
      }
      $msg = sprintf('Notify API: new \Alphagov\Notifications\Client(%s): %s', $api_key, $msg);
      $this->logAndException($msg);
    }

    $state_key = "fsa_notify.template_email";
    $this->email_template_id = \Drupal::state()->get($state_key);
    if (empty($this->email_template_id)) {
      $msg = sprintf('Notify API Template ID not specified in state "%s".', $state_key);
      $this->logAndException($msg);
    }

    $state_key = "fsa_notify.template_sms";
    $this->sms_template_id = \Drupal::state()->get($state_key);
    if (empty($this->sms_template_id)) {
      $msg = sprintf('Notify API Template ID not specified in state "%s".', $state_key);
      $this->logAndException($msg);
    }
  
  }
  
  public function email(User $user, string $reference, array $personalisation) {
  
    $email = $user->getEmail();
  
    $login = Url::fromRoute('user.login', [], ['absolute' => TRUE]);
    $login = $login->toString();
    $personalisation['login'] = $login;
  
    $unsubscribe = 'http://.../unsubscribe';
    $personalisation['unsubscribe'] = $unsubscribe;
    
    try {
      $this->api->sendEmail(
        $email,
        $this->email_template_id,
        $personalisation,
        $reference
      );
    }
    catch (Exception $e) {
      $msg = $e->getMessage();
      if (!is_string($msg)) {
        $msg = print_r($msg, TRUE);
      }
      $msg = sprintf('Notify API: sendEmail(%s): %s', $email, $msg);
      $this->logAndException($msg);
    }
  }
  
  public function sms(User $user, string $reference, array $personalisation) {

    $phoneNumber = $user->field_notification_sms->getString();
    // Phone number is optional field!
    if (empty($phoneNumber)) {
      return;
    }

    try {
      $this->api->sendSms(
        $phoneNumber,
        $this->sms_template_id,
        $personalisation,
        $reference
      );
    }
    catch (Exception $e) {
      $msg = $e->getMessage();
      if (!is_string($msg)) {
        $msg = print_r($msg, TRUE);
      }
      $msg = sprintf('Notify API: sendSms(%s): %s', $phoneNumber, $msg);
      $this->logAndException($msg);
    }
  }

  protected function logAndException(string $msg) {
    \Drupal::logger('fsa_notify')->error($msg);
    throw new \Exception($msg);
  }

}
