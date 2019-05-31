<?php

/**
 * @file
 * Create alerts for testing.
 */

use Drupal\node\Entity\Node;
use Drupal\Component\Utility\Random;

$terms = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree('alerts_allergen', 0, 1);
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

// Get type from argument.
if (in_array(end($_SERVER['argv']), ['AA', 'PRIN', 'FAFA'])) {
  $alert_type = end($_SERVER['argv']);
}
else {
  $alert_type = 'AA';
}

$random_words = [
  Random::word(rand(3, 10)),
  Random::word(rand(3, 10)),
];
$title = $random_words[0] . ' ' . $random_words[1];

$dt = new DateTime();

$node = Node::create([
  'type'        => 'alert',
  'title'       => $alert_type . ' Alert: ' . $title,
  'field_alert_notation' => $alert_type . '-' . $random_words[0],
  'field_alert_type' => $alert_type,
  'field_alert_allergen' => $allergys,
  'field_alert_smstext' => $alert_type . ': ' . $title,
  'field_alert_description' => 'This is a test-alert',
  'field_alert_riskstatement' => $alert_type . ': ' . $title,
  'field_alert_modified' => $dt->format('Y-m-d\TH:i:s'),
]);
$node->save();

printf("Created %s: %s\nnode/%d\n", $alert_type, $title, $node->id());
