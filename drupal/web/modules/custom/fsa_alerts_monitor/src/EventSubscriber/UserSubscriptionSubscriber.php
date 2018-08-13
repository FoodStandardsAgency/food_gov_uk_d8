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

    $signinService = \Drupal::service('fsa_signin.service');

    // Delivery method is the same for all alerts.
    $delivery_method = $signinService->alertDeliveryMethod($user);

    // Flatten empty array.
    if (empty($delivery_method)) {
      $delivery_method = '';
    }

    $fields = [
      'uid' => $user->id(),
      'activity' => 'Subscription',
      'created' => $user->getCreatedTime(),
      'food_alerts' => implode(',', $signinService->subscribedFoodAlerts($user)),
      'food_alert_medium' => $delivery_method,
      'allergy_alerts' => implode(',', $signinService->subscribedTermIds($user)),
      'allergy_alert_medium' => $delivery_method,
      'news_alerts' => implode(',', $signinService->subscribedNewsAlerts($user)),
      'news_alert_medium' => $delivery_method,
      'consultation_alerts' => implode(',', $signinService->subscribedConsultationsAlerts($user)),
      'consultation_alert_medium' => $delivery_method,
    ];

    \Drupal::database()->insert('fsa_alerts_monitor')->fields($fields)->execute();

  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events['fsa_alerts_monitor.user.subscribe'][] = ['logNewSubscriber'];
    return $events;
  }

}
