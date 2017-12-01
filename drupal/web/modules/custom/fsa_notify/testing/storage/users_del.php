<?php

/**
 * @file
 * Delete all test users.
 */

// Drush scr $(pwd)/users_del.php
$query = \Drupal::entityQuery('user');
$query->condition('mail', '%@example.com', 'like');
$uids = $query->execute();

$chunks = array_chunk($uids, 100);

$i = 1;
foreach ($chunks as $uids2) {
  user_delete_multiple($uids2);
  printf("\r%03.2f%%", 100.0 * $i++ / count($chunks));
}
print "\n";
