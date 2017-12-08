<?php

namespace Drupal\fsa_signin\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\taxonomy\Entity\Term;

/**
 * Provides a 'MySubscriptions' block.
 *
 * @Block(
 *  id = "my_subscriptions",
 *  admin_label = @Translation("My subscriptions"),
 * )
 */
class MySubscriptions extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];

    // Disable block cache.
    $build['#cache'] = ['max-age' => 0];

    // Get tempstore for checking check if user has subscribed to anything on
    // previous pages.
    $tempstore = \Drupal::service('user.private_tempstore')->get('fsa_signin');

    $tempstore->get('food_alert_registration');
    $tempstore->get('alert_tids_for_registration');
    $tempstore->get('news_tids_for_registration');

    // Food alerts to a list.
    if (!empty($tempstore->get('food_alert_registration'))) {
      $food_items = [];
      foreach ($tempstore->get('food_alert_registration') as $value) {
        $term = Term::load($value);
        $name = $term->getName();
        $food_items[] = ['#markup' => $name];
      }
      $food_list = [
        '#theme' => 'item_list',
        '#list_type' => 'ul',
        '#attributes' => [
          'class' => [
            'item',
          ],
        ],
        '#items' => $food_items,
      ];
    }

    // Alerts to a list.
    if (!empty($tempstore->get('alert_tids_for_registration'))) {
      $alert_items = [];
      foreach ($tempstore->get('alert_tids_for_registration') as $value) {
        $term = Term::load($value);
        $name = $term->getName();
        $alert_items[] = ['#markup' => $name];
      }
      $alert_list = [
        '#theme' => 'item_list',
        '#list_type' => 'ul',
        '#attributes' => [
          'class' => [
            'item',
          ],
        ],
        '#items' => $alert_items,
      ];
    }

    // News to a list.
    if (!empty($tempstore->get('news_tids_for_registration'))) {
      $news_items = [];
      foreach ($tempstore->get('news_tids_for_registration') as $value) {
        $term = Term::load($value);
        $name = $term->getName();
        $news_items[] = ['#markup' => $name];
      }
      $news_list = [
        '#theme' => 'item_list',
        '#list_type' => 'ul',
        '#attributes' => [
          'class' => [
            'item',
          ],
        ],
        '#items' => $news_items,
      ];
    }

    // $food_list . $alert_list . $news_list.
    return $alert_list;
  }

}
