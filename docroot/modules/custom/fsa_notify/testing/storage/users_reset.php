<?php

/**
 * @file
 * Reset users.
 */

use Drupal\user\Entity\User;

// Use first argument of script for continuation.
$min_uid = drush_shift();
if (empty($min_uid)) {
  $min_uid = 0;
}

$methods = [
  'none',
  'sms',
  'immediate',
  'daily',
  'weekly',
];

$terms = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree('alerts_allergen');
$terms = array_map(function ($t) {
  return $t->tid;
}, $terms);
sort($terms);

$query = \Drupal::entityQuery('node');
$query->condition('type', 'alert');
$query->sort('nid');
$alerts = $query->execute();

$query = \Drupal::entityQuery('user');
$query->condition('mail', '%@example.com', 'like');
$query->condition('uid', $min_uid, '>=');
$query->sort('uid');
$uids = $query->execute();
// Init.
stats(count($uids));
foreach ($uids as $uid) {
  stats($uid);
  $u = User::load($uid);

  // Method.
  $method = $methods[array_rand($methods)];
  $u->field_notification_method = $method;

  // Allergys.
  $allergys = [];
  if ($method != 'none') {
    $rand = rand(1, 5);
    $rand = array_rand($terms, $rand);
    if (!is_array($rand)) {
      // If array_rand() returns one element, its not array.
      $rand = [$rand];
    }
    foreach ($rand as $k) {
      $allergys[] = $terms[$k];
    }
  }
  $allergys = empty($allergys) ? NULL : $allergys;
  $u->field_subscribed_notifications = $allergys;

  // Notification cache.
  $rand = mt_rand() / mt_getrandmax();
  $cache = [];
  if ($method != 'none' && $rand > 0.2) {
    $rand = rand(1, count($alerts));
    $rand = array_rand($alerts, $rand);
    if (!is_array($rand)) {
      // If array_rand() returns one element, its not array.
      $rand = [$rand];
    }
    foreach ($rand as $k) {
      $cache[] = $alerts[$k];
    }
  }
  $cache = empty($cache) ? NULL : $cache;
  $u->field_notification_cache = $cache;

  // Status.
  $rand = mt_rand() / mt_getrandmax();
  $rand > 0.1 ? $u->activate() : $u->block();

  if ($u->field_notification_method->getString() == 'sms') {
    $u->field_notification_method = '+44999999999999';
  }

  $u->save();
}
print "\n";

/**
 * First time called, uid is count.
 */
function stats($uid) {

  static $count;
  static $i;
  static $start;

  if (empty($count)) {
    $count = $uid;
    $i = 1;
    $start = microtime(TRUE);
    return;
  }

  $now = microtime(TRUE);
  $percent = 100 * $i / $count;
  $elapsed = $now - $start;
  $speed = $i / $elapsed;
  $total = $count / $speed;
  $togo = $total - $elapsed;
  printf("\ruid=%d percent=%.3f items=%d/%d speed=%.2f elapsed=%s togo=%s total=%s   ", $uid, $percent, $i, $count, $speed, human_time($elapsed), human_time($togo), human_time($total));
  $i++;
}

/**
 * Human-readable time.
 *
 * @param int $sec
 *   Time in seconds.
 *
 * @return string
 *   Human redable time.
 */
function human_time($sec) {
  $h = floor($sec / 3600);
  $sec -= $h * 3600;
  $m = floor($sec / 60);
  $sec -= $m * 60;
  return sprintf("%d:%02d:%02.2f", $h, $m, $sec);
}
