<?php

namespace Drupal\fsa_notify;

use Drupal\node\Entity\Node;
use Drupal\user\Entity\User;

/**
 * Class FsaNotifyStorage.
 *
 * @package Drupal\fsa_notify
 */
class FsaNotifyStorage {

  /**
   * Get all types.
   *
   * Due do large volume of data (entity loads) system tends to crash -- out of
   * memory therefore need batching / chunking it returns an array which has
   * keys as uid and value as nids if no more digests are left, it return empty
   * array.
   *
   * @param string $type
   *   Type to get.
   * @param int $batch_size
   *   Batch size to process.
   *
   * @return array
   *   Notifications to send.
   */
  public function getAllType(string $type, int $batch_size = 1000) {

    $query = \Drupal::entityQuery('user');
    $query->condition('uid', 0, '>');
    $query->condition('status', 1);

    if ($type == 'sms') {
      // Get users who prefer SMS's.
      $query->condition('field_delivery_method', $type, '=');
    }
    else {
      // And email subscriber's with their preferred frequency.
      $query->condition('field_email_frequency', $type);
    }

    $query->Exists('field_notification_cache');
    $query->range(0, $batch_size);
    // $query->sort('uid');.
    $uids = $query->execute();

    $notifications = [];
    foreach ($uids as $uid) {
      $u = User::load($uid);
      $nids = $u->field_notification_cache->getValue();
      $nids = array_map(
        function ($nid) {
          return (int) $nid['target_id'];
        },
        $nids
      );
      $nids = array_unique($nids);
      $notifications[$uid] = $nids;
    }

    // Try to kill caches to prevent succumb to pressures of the memory.
    \Drupal::entityManager()->getStorage('user')->resetCache();

    return $notifications;
  }

  /**
   * Store nodes for notify sending to all relevant users.
   *
   * Queries are done by matching the users subscribe term preference existence
   * on the nodes queued for sending.
   *
   * @param \Drupal\node\Entity\Node $node
   *   The alert node.
   */
  public function store(Node $node) {

    $uids = [];
    $nid = $node->id();

    // Store alerts for sending.
    if ($node->hasField('field_alert_type')) {

      // Get the alert type, one of AA, PRIN or FAFA.
      $alert_type = $node->field_alert_type->value;

      // Allergy alerts.
      if ($alert_type === 'AA' && $node->hasField('field_alert_allergen')) {
        // Store allergy alerts.
        $allergens = array_map(
          function ($a) {
            return $a['target_id'];
          },
          $node->field_alert_allergen->getValue()
        );

        if (!empty($allergens)) {
          $query = $this->queryUsersWithSubscribePreferences('field_subscribed_notifications', $allergens, 'allergy');
          $uids = $query->execute();
        }
      }
      elseif (in_array($alert_type, ['FAFA', 'PRIN'])) {
        // Rest are food alerts, get user's prefs to store for sending.
        $query = $this->queryUsersWithSubscribePreferences('field_subscribed_food_alerts', ['all'], 'food');
        $uids = $query->execute();
      }
    }

    // Store news items for sending.
    if ($node->hasField('field_news_type')) {
      $news_types = array_map(
        function ($n) {
          return $n['target_id'];
        },
        $node->field_news_type->getValue()
      );

      if (!empty($news_types)) {
        $query = $this->queryUsersWithSubscribePreferences('field_subscribed_news', $news_types, 'news');
        $uids = $query->execute();
      }
    }

    // Store consultations for sending.
    if ($node->hasField('field_consultations_type_alert')) {
      $consultations_type = $node->field_consultations_type_alert->getValue();
      $consultations_type = array_map(
        function ($c) {
          return $c['target_id'];
        },
        $consultations_type
      );

      if (!empty($consultations_type)) {
        $query = $this->queryUsersWithSubscribePreferences('field_subscribed_cons', $consultations_type, 'consultation');
        $uids = $query->execute();
      }
    }

    foreach ($uids as $uid) {
      $u = User::load($uid);
      $u->field_notification_cache[] = $nid;
      $u->save();
    }

    \Drupal::entityManager()->getStorage('user')->resetCache();

  }

  /**
   * Query users with their subscribe preferences (term ref values).
   *
   * @param string $field
   *   The name of the field to filter.
   * @param array $values
   *   Array of values for the field.
   * @param string $type
   *   The type of content to identify the notification method preference.
   *
   * @return \Drupal\Core\Entity\Query\QueryInterface
   *   Query object.
   */
  protected function queryUsersWithSubscribePreferences($field, array $values, $type) {

    // Build the basics for getting user subscribe preferences.
    $query = \Drupal::entityQuery('user');
    $query->condition('uid', 0, '>');
    $query->condition('status', 1);
    $query->condition('field_email_frequency', 'none', '!=');
    // Filter the users who have their checkboxes for receiving with certain
    // delivery methods.
    switch ($type) {
      case 'allergy':
      case 'food':
        $query->condition('field_delivery_method', NULL, 'IS NOT');
        break;

      case 'news':
      case 'consultation':
        $query->condition('field_delivery_method_news', NULL, 'IS NOT');
        break;

    }

    // Get the user's subscribe preferences for type of the content to be stored
    // for sending.
    $query->condition($field, $values, 'in');

    return $query;
  }

  /**
   * Clear cache of notifications for particular user.
   *
   * @param \Drupal\user\Entity\User $user
   *   User object.
   * @param bool $save
   *   If user should be saved.
   */
  public function reset(User $user, bool $save = TRUE) {
    $user->field_notification_cache = NULL;
    if ($save) {
      $user->save();
    }
  }

}
