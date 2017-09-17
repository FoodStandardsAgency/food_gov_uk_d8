<?php

// https://github.com/alphagov/notifications-php-client

namespace Drupal\fsa_notify;

use Alphagov\Notifications\Exception\ApiException;
use Alphagov\Notifications\Exception\NotifyException;
use Drupal\Core\Url;
use Drupal\user\Entity\User;

abstract class FsaNotifyAPI {

  protected $api = NULL;
  protected $template_id = NULL;

  public function __construct($template_state_key) {
  
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

    $state_key = $template_state_key;
    $this->template_id = \Drupal::state()->get($state_key);
    if (empty($this->template_id)) {
      $msg = sprintf('Notify API Template ID not specified in state "%s".', $state_key);
      $this->logAndException($msg);
    }
  }
  
  abstract public function send(User $user, string $reference, array $personalisation);
  
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
