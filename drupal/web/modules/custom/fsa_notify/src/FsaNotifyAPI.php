<?php

// https://github.com/alphagov/notifications-php-client

namespace Drupal\fsa_notify;

use Alphagov\Notifications\Exception\ApiException;
use Alphagov\Notifications\Exception\NotifyException;
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
      $msg = sprintf('Notify API: new \Alphagov\Notifications\Client(%s)', $api_key);
      $this->api = new \Alphagov\Notifications\Client([
        'apiKey' => $api_key,
        'httpClient' => new \Http\Adapter\Guzzle6\Client,
      ]);
    }
    catch (ApiException $e) {
      $this->api = NULL;
      $this->logAndException($msg, $e);
    }
    catch (Exception $e) {
      $this->api = NULL;
      $this->logAndException($msg, $e);
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
      $msg = sprintf('Notify API: sendEmail(%s)', $email);
      $this->api->sendEmail(
        $email,
        $this->email_template_id,
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
  
  public function sms(User $user, string $reference, array $personalisation) {

    $phoneNumber = $user->field_notification_sms->getString();
    // Phone number is optional field!
    if (empty($phoneNumber)) {
      return;
    }

    try {
      $msg = sprintf('Notify API: sendSms(%s)', $email);
      $this->api->sendSms(
        $phoneNumber,
        $this->sms_template_id,
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

  protected function logAndException(string $msg, $e = NULL) {
    $log = $msg;
    if (!empty($e)) {
      $msg = $e->getMessage();
      if (!is_string($msg)) {
        $msg = print_r($msg, TRUE);
      }
      $log = sprintf('%s :: %s', $log, $msg);
    }
    \Drupal::logger('fsa_notify')->error($log);
    throw new \Exception($log);
  }

}
