<?php

namespace Drupal\fsa_notify;

use Alphagov\Notifications\Exception\ApiException;
use Alphagov\Notifications\Client as AlphaGovClient;
use Http\Adapter\Guzzle6\Client as GuzzleClient;
use Drupal\user\Entity\User;

/**
 * Defines base implementation for FSA Notify API.
 *
 * @see https://github.com/alphagov/notifications-php-client
 */
abstract class FsaNotifyAPI {

  protected $api = NULL;
  protected $templateId = NULL;
  protected $templateIdCy = NULL;

  /**
   * {@inheritdoc}
   */
  public function __construct($template_state_key, $template_state_key_email_cy = FALSE) {

    $state_key = 'fsa_notify.api';
    $api_key = \Drupal::state()->get($state_key);
    if (empty($api_key)) {
      $msg = sprintf('Notify API key not specified in state "%s".', $state_key);
      $this->logAndException($msg);
    }

    try {
      $msg = sprintf('Notify API: new \Alphagov\Notifications\Client(%s)', $api_key);
      $this->api = new AlphaGovClient([
        'apiKey' => $api_key,
        'httpClient' => new GuzzleClient(),
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

    // Get template id's we want to use. The Welsh template (cy) may be empty
    // since SMS does not use template translation.
    $this->templateId = \Drupal::state()->get($template_state_key);
    // If Welsh template id is not set use the default.
    $this->templateIdCy = $template_state_key_email_cy ? \Drupal::state()->get($template_state_key_email_cy) : FALSE;

    // Make sure we have a templateId.
    if (empty($this->templateId) && empty($this->templateIdCy)) {
      $msg = sprintf('Notify API Template ID not specified');
      $this->logAndException($msg);
    }
  }

  /**
   * Abstract to send the message.
   *
   * @param \Drupal\user\Entity\User $user
   *   User account.
   * @param string $reference
   *   Reference id.
   * @param array $personalisation
   *   Notify personalization array.
   *
   * @return mixed
   *   todo: document the possible return values.?
   */
  abstract public function send(User $user, string $reference, array $personalisation);

  /**
   * Log error and throw exception.
   *
   * @param string $msg
   *   Message to log.
   * @param object|null $e
   *   The error.
   *
   * @throws \Exception
   */
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
