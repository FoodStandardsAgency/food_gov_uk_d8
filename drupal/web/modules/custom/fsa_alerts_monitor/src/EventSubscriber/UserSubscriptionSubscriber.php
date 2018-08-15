<?php

namespace Drupal\fsa_alerts_monitor\EventSubscriber;

use Drupal\fsa_signin\Event\UserSubscriptionEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class UserSubscriptionSubscriber implements EventSubscriberInterface {

  /**
   * Callback for this event to register a new subscription.
   *
   * @param \Drupal\fsa_signin\Event\UserSubscriptionEvent $event
   *   Event object containing our data.
   * @throws \Exception
   */
  public function logNewSubscriber(UserSubscriptionEvent $event) {
    // Unwrap the user object from the event.
    $user = $event->getUser();

    $alert_monitor = \Drupal::service('fsa_alerts_monitor.service');
    $alert_monitor->trackEvent($user, 'Subscription', \Drupal::time()->getCurrentTime());
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events['fsa_alerts_monitor.user.subscribe'][] = ['logNewSubscriber'];
    return $events;
  }

}
