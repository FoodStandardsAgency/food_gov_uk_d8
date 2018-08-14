<?php

namespace Drupal\fsa_alerts_monitor\EventSubscriber;

use Drupal\fsa_signin\Event\UserUpdatePreferencesEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class UserUpdatePreferencesSubscriber implements EventSubscriberInterface {

  /**
   * Callback for this event to register an preferences update event.
   *
   * @param \Drupal\fsa_signin\Event\UserUpdatePreferencesEvent $event
   *   Event object containing our data.
   * @throws \Exception
   */
  public function logUpdatePreferences(UserUpdatePreferencesEvent $event) {
    // Unwrap the user object from the event.
    $user = $event->getUser();

    $alert_monitor = \Drupal::service('fsa_alerts_monitor.service');
    $alert_monitor->trackEvent($user, 'Update preferences', \Drupal::time()->getCurrentTime());
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events['fsa_alerts_monitor.user.update_preferences'][] = ['logUpdatePreferences'];
    return $events;
  }

}
