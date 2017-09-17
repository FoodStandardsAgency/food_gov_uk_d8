<?php

namespace Drupal\fsa_notify;

use Drupal\Core\Url;
use Drupal\node\Entity\Node;
use Drupal\user\Entity\User;

class FsaNotifyStorage {

  // due do large volume of data (entity loads)
  // system tends to crash -- out of memory
  // therefore need batching / chunking
  // it returns an array which has keys as uid and value as nids
  // if no more digests are left, it return empty array
  public function getAllType(string $type, int $batch_size = 1000) {

    $query = \Drupal::entityQuery('user');
    $query->condition('uid', 0, '>');
    $query->condition('status', 1);
    $query->condition('field_notification_method', $type);
    $query->Exists('field_notification_cache');
    $query->range(0, $batch_size);
    // $query->sort('uid');
    $uids = $query->execute();

    $notifications = [];
    foreach ($uids as $uid) {
      $u = User::load($uid);
      $nids = $u->field_notification_cache->getValue();
      $nids = array_map(function ($nid) {return (int) $nid['target_id'];}, $nids);
      $nids = array_unique($nids);
      $notifications[$uid] = $nids;
    }

    // try to kill caches
    // to prevent succumb to pressures of the memory
    \Drupal::entityManager()->getStorage('user')->resetCache();

    return $notifications;
  }

  // store alert to all relevant users
  // by matching allergy taxonomy terms
  public function store(Node $alert) {

    $nid = $alert->id();
    $allergens = $alert->field_alert_allergen->getValue();
    $allergens = array_map(function ($a) {return $a['target_id'];}, $allergens);

    $query = \Drupal::entityQuery('user');
    $query->condition('uid', 0, '>');
    $query->condition('status', 1);
    $query->condition('field_notification_method', 'none', '!=');
    $query->condition('field_notification_allergys', $allergens, 'in');
    // $query->sort('uid');
    $uids = $query->execute();

    foreach ($uids as $uid) {
      $u = User::load($uid);
      $u->field_notification_cache[] = $nid;
      $u->save();
    }

    \Drupal::entityManager()->getStorage('user')->resetCache();

  }

  // clear cache of notifications for particular user
  public function reset(User $user, bool $save = TRUE) {
    $user->field_notification_cache = NULL;
    if ($save) {
      $user->save();
    }
  }

}
