<?php

/**
 * @file
 * Test-srcipt to add one alert.
 */

use Drupal\user\Entity\User;

$uid = 123259;
$count = 2;

$query = \Drupal::entityQuery('node');
$query->condition('type', 'alert');
$query->condition('status', 1);
$nids = $query->execute();

shuffle($nids);
$nids = array_slice($nids, 0, $count);

$u = User::load($uid);
$u->field_notification_cache = $nids;
$u->save();
