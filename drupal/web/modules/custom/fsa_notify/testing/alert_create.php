<?php

/**
 * @file
 * Create alerts for testing.
 */

use Drupal\node\Entity\Node;
use Drupal\Component\Utility\Random;

$terms = \Drupal::entityManager()->getStorage('taxonomy_term')->loadTree('alerts_allergen');
$terms = array_map(function ($t) {
  return $t->tid;
}, $terms);
sort($terms);

$allergys = [];
$rand = rand(1, 2);
$rand = array_rand($terms, $rand);
if (!is_array($rand)) {
  // If array_rand() returns one element, its not array.
  $rand = [$rand];
}
foreach ($rand as $k) {
  $allergys[] = $terms[$k];
}

// One of 'AA', 'PRIN' or 'FAFA'.
$alert_type = 'AA';
$notation = Random::name(15);
$node = Node::create([
  'type'        => 'alert',
  'title'       => $alert_type . ' alert ' . $notation,
  'field_alert_notation' => $notation,
  'field_alert_type' => $alert_type,
  'field_alert_allergen' => $allergys,
  'field_alert_smstext' => $notation,
]);
$node->save();

printf("nid=%d\n", $node->id());
