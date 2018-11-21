<?php

namespace Drupal\fsa_alerts_monitor;

/**
 * @file
 */

use Drupal\user\Entity\User;

class FsaAlertsMonitorService {

  /**
   * FsaAlertsMonitorService constructor.
   */
  public function __construct() {
  }

  /**
   * Subscription event tracker function to simplify registering activity.
   *
   * @param \Drupal\user\Entity\User $user
   *   The user object.
   * @param \Drupal\fsa_alerts_monitor\string $activity
   *   Simple string outlining what activity took place (Subscribe/update etc)
   * @param \Drupal\fsa_alerts_monitor\int $timestamp
   *   UNIX timestamp of activity time.
   *
   * @throws \Exception
   */
  public function trackEvent(User $user, string $activity, int $timestamp) {

    $signin_service = \Drupal::service('fsa_signin.service');

    // Delivery method is the same for all alerts.
    $delivery_method = implode(',', array_filter($signin_service->alertDeliveryMethods($user)));
    $news_delivery_method = $signin_service->newsDeliveryMethod($user);
    $food_alerts = implode(',', array_filter($signin_service->subscribedFoodAlerts($user)));
    $allergy_alerts = implode(',', array_filter($signin_service->subscribedTermIds($user)));
    $news_alerts = implode(',', array_filter($signin_service->subscribedNewsAlerts($user)));
    $consultation_alerts = implode(',', array_filter($signin_service->subscribedConsultationsAlerts($user)));

    // Screen for any null values.
    $fields = [
      'delivery_method',
      'news_delivery_method',
      'food_alerts',
      'allergy_alerts',
      'news_alerts',
      'consultation_alerts',
    ];

    foreach ($fields as $field) {
      if (empty(${$field})) {
        ${$field} = '';
      }
    }

    $fields = [
      'uid' => $user->id(),
      'activity' => $activity,
      'created' => $timestamp,
      'food_alerts' => $food_alerts,
      'food_alert_medium' => $delivery_method,
      'allergy_alerts' => $allergy_alerts,
      'allergy_alert_medium' => $delivery_method,
      'news_alerts' => $news_alerts,
      'news_alert_medium' => $news_delivery_method,
      'consultation_alerts' => $consultation_alerts,
      'consultation_alert_medium' => $delivery_method,
    ];

    \Drupal::database()
      ->insert('fsa_alerts_monitor')
      ->fields($fields)
      ->execute();

  }

}
