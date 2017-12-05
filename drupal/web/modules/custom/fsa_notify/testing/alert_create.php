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

$node = Node::create([
  'type'        => 'alert',
  'title'       => Random::name(32),
  'field_alert_notation' => Random::name(32),
  'field_alert_allergen' => $allergys,
]);
$node->save();

printf("nid=%d\n", $node->id());
