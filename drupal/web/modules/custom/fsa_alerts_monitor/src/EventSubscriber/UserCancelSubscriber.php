<?php

namespace Drupal\fsa_alerts_monitor\EventSubscriber;

use Drupal\fsa_signin\Event\UserCancelEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class UserCancelSubscriber implements EventSubscriberInterface {

  /**
   * Callback for this event to register when a user cancels their
   * account via the profile page(s).
   *
   * @param \Drupal\fsa_signin\Event\UserCancelEvent $event
   *   Event object containing our data.
   * @throws \Exception
   */
  public function logCancellation(UserCancelEvent $event) {
    // Unwrap the user object from the event.
    $user = $event->getUser();

    $alert_monitor = \Drupal::service('fsa_alerts_monitor.service');
    $alert_monitor->trackEvent($user, 'Cancel account', \Drupal::time()->getCurrentTime());
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events['fsa_alerts_monitor.user.cancel'][] = ['logCancellation'];
    return $events;
  }

}
