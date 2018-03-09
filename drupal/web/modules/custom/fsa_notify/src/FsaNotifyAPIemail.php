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
    parent::__construct($state_key);
  }

  /**
   * {@inheritdoc}
   */
  public function send(User $user, string $reference, array $personalisation) {

    $email = $user->getEmail();

    try {
      $msg = sprintf('Notify API: sendEmail(%s)', $email);

      // Add the user parameters to the unsubscribe URL.
      $personalisation['unsubscribe'] = $personalisation['unsubscribe'] . '?' . UrlHelper::buildQuery(['id' => $user->id(), 'email' => $email]);

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
