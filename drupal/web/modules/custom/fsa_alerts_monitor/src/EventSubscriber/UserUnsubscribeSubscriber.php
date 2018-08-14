<?php

namespace Drupal\fsa_alerts_monitor\EventSubscriber;

use Drupal\fsa_signin\Event\UserUnsubscribeEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class UserUnsubscribeSubscriber implements EventSubscriberInterface {

  /**
   * Callback for this event to register an unsubscribe event.
   *
   * @param \Drupal\fsa_signin\Event\UserSubscriptionEvent $event
   *   Event object containing our data.
   * @throws \Exception
   */
  public function logUnsubscribe(UserUnsubscribeEvent $event) {
    // Unwrap the user object from the event.
    $user = $event->getUser();

    $alert_monitor = \Drupal::service('fsa_alerts_monitor.service');
    $alert_monitor->trackEvent($user, 'Unsubscribe', \Drupal::time()->getCurrentTime());
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events['fsa_alerts_monitor.user.unsubscribe'][] = ['logUnsubscribe'];
    return $events;
  }

}
