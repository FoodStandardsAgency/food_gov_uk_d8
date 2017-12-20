<?php
/**
 * @file
 * My spaghetti testing file.
 */

/*
$phone = '+358 44555 0010';
if (preg_match('/^\+[0-9 ]{7,}$/', $phone)) {
  drush_print('match');
}
else {
  drush_print('no match');
}
*/



/** @var \Drush\Queue\QueueInterface $queue */
/**
$queue = \Drupal::service('queue')->get('elasticsearch_helper_indexing');
// Remove all items from the queue.
$queue->deleteQueue();
*/

//use \Drupal\Core\Link;
//$config = \Drupal::config('config.fsa_alerts_import');
//$api_base_path = $config->get('api_url');

/*
$options = ['attributes' => ['class' => 'back-link']];
$link = Link::createFromRoute(t('Back'), '<front>', [], $options);
return ['#markup' => $link];

drush_print_r($link);
*/

// different food alert types are allowed to subscribe to.
//$alert_types = [
//  'all' => t('Food alerts')->render(),
//];
//
//$options = [];
//foreach ($alert_types as $key => $name) {
//  $options[$key] = $name;
//}
//
//drush_print_r($options);
//
//
//$all_terms = \Drupal::entityTypeManager()
//  ->getStorage('taxonomy_term')
//  ->loadTree('alerts_allergen', 0, 1, FALSE);
//$options = [];
//foreach ($all_terms as $term) {
//  $options[$term->tid] = t($term->name)->render();
//}
//drush_print_r($options);


//drush_print_r(date('d.m.Y', '1511136000'));
//
//$state_key = 'notify.foobar';
//drush_print(sprintf('Notify API key not specified in state "%s".', $state_key));

/*
$query = \Drupal::entityQuery('user');
$query->condition('uid', 0, '>');
$query->condition('status', 1);
$query->condition('field_notification_sms', '+358445550010', '=');
$uids = $query->execute();
drush_print_r($uids);

$uids = user_load_by_mail('timo.testaa@example.com');
$uid = $uids->id();
$uids = [$uid => $uid];
drush_print_r($uids);
*/

//var_dump(Drupal\Component\Utility\Crypt::randomBytesBase64(16));
//
//$query = \Drupal::entityQuery('user');
//$query->condition('uid', 0, '>');
//$query->condition('status', 1);
//$query->condition('field_notification_sms', '+358445550010', '=');
//$uids = $query->execute();
//
//foreach ($uids as $uid) {
//  drush_print($uid);
//}

/*
use \Drupal\Core\Link;

$link = Link::createFromRoute(t('Text'), 'entity.node.canonical', ['node' => '24'], ['query' => ['my-awesome-subpage' => FALSE]]);
var_dump($link->toString());
//\Drupal\Core\Link::fromTextAndUrl('ads','a sd');
*/


/*
$urls = [
  'http://data.food.gov.uk/food-alerts/def/Alert',
  'http://data.food.gov.uk/food-alerts/def/PRIN',
  'http://data.food.gov.uk/food-alerts/def/AA',
  'http://data.food.gov.uk/food-alerts/def/Alert',
  'http://data.food.gov.uk/food-alerts/def/AA',
  'http://data.food.gov.uk/food-alerts/def/Alert',
  'http://data.food.gov.uk/food-alerts/def/Alert',
  'FAFA'];

foreach ($urls as $url) {
  $url = rtrim($url, '/');

  // Get last segment from the URI resource
  // (should work even if entry is not URL.
  preg_match('/([^\/]*)$/', $url, $type);
  var_dump($type);
}
*/

