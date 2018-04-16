<?php

namespace Drupal\fsa_notify;

use Drupal\node\Entity\Node;
use Drupal\user\Entity\User;
use Drupal\Core\Database\Database;

/**
 * Class FsaNotifyStorageDBConnection.
 *
 * Uses Direct database connection to save notification cache fields
 * to prevent out of memory errors by using too many calls to User::load
 *
 * @package Drupal\fsa_notify
 */
class FsaNotifyStorageDBConnection extends FsaNotifyStorage {

  /**
   * Store nodes for notify sending to all relevant users.
   *
   * Queries are done by matching the users subscribe term preference existence
   * on the nodes queued for sending.
   *
   * @param \Drupal\node\Entity\Node $node
   *   The alert node.
   * @param string $lang
   *   Language code.
   */
  public function store(Node $node, $lang) {

    $uids = [];
    $nid = $node->id();
    $node_type = $node->getType();

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
          $query = $this->queryUsersWithSubscribePreferences('field_subscribed_notifications', $allergens, 'allergy', $lang);
          $uids = $query->execute();
        }
      }
      elseif (in_array($alert_type, ['FAFA', 'PRIN'])) {
        // Rest are food alerts, get user's prefs to store for sending.
        $query = $this->queryUsersWithSubscribePreferences('field_subscribed_food_alerts', ['all'], 'food', $lang);
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
        $query = $this->queryUsersWithSubscribePreferences('field_subscribed_news', $news_types, 'news', $lang);
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
        $query = $this->queryUsersWithSubscribePreferences('field_subscribed_cons', $consultations_type, 'consultation', $lang);
        $uids = $query->execute();
      }
    }

    $connection = Database::getConnection();
    $options = [];
    foreach ($uids as $uid) {
      $delivery_methods = $connection->query('SELECT field_delivery_method_value FROM user__field_delivery_method WHERE entity_id = :entity_id',
        [':entity_id' => $uid],
        $options
      )->fetchAll();

      foreach($delivery_methods as $delivery_method) {
        if ($delivery_method->field_delivery_method_value == 'sms' && $node_type == 'alert') {

          $delta = $connection->query('select max(delta) as max_delta from user__field_notification_cache_sms where entity_id = :entity_id',
            [':entity_id' => $uid],
            $options
          )->fetchColumn();

          if ($delta === NULL) {
            $delta = 0;
          }
          else {
            $delta++;
          }

            $connection->query("INSERT INTO user__field_notification_cache_sms (bundle, deleted, entity_id, revision_id, langcode, delta, field_notification_cache_sms_target_id) values ('user', 0, :entity_id, :entity_id, 'en', :delta, :nid)",
              [
                ':delta' => $delta,
                ':entity_id' => $uid,
                ':nid' => $nid,
              ],
              $options
            );
        }
        elseif ($delivery_method->field_delivery_method_value == 'email') {
          $delta = $connection->query('select max(delta) as max_delta from user__field_notification_cache where entity_id = :entity_id',
            [':entity_id' => $uid],
            $options
          )->fetchColumn();

          if ($delta === NULL) {
            $delta = 0;
          }
          else {
            $delta++;
          }

          $connection->query("INSERT INTO user__field_notification_cache (bundle, deleted, entity_id, revision_id, langcode, delta, field_notification_cache_target_id) values ('user', 0, :entity_id, :entity_id, 'en', :delta, :nid)",
            [
              ':delta' => $delta,
              ':entity_id' => $uid,
              ':nid' => $nid,
            ],
            $options
          );
        }
      }
    }
  }

}
