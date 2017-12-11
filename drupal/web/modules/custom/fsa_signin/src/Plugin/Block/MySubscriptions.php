<?php

namespace Drupal\fsa_signin\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\taxonomy\Entity\Term;

/**
 * Provides 'My Subscriptions' block for registration flow.
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

    // Food alerts to a list.
    $food_list = [];
    if (count($tempstore->get('food_alert_registration')) > 0) {
      $food_items = $tempstore->get('food_alert_registration');
      $food_list = $this->itemListFromTerms(
        $food_items,
        $this->t('Food alerts')
      );
    }

    // Alerts to a list.
    $alert_list = [];
    if (count($tempstore->get('alert_tids_for_registration')) > 0) {
      $alert_items = $tempstore->get('alert_tids_for_registration');
      // Alerts are taxonomy terms, use the protected func.
      $alert_list = $this->itemListFromTerms(
        $alert_items,
        $this->t('Allergy alerts')
      );
    }

    // News to a list.
    $news_list = [];
    if (count($tempstore->get('news_tids_for_registration')) > 0) {
      $news_items = $tempstore->get('news_tids_for_registration');
      // News are taxonomy terms, use the protected func.
      $news_list = $this->itemListFromTerms(
        $news_items,
        $this->t('News')
      );
    }

    // Consultations to a list.
    $cons_list = [];
    if (count($tempstore->get('cons_tids_for_registration')) > 0) {
      $cons_items = $tempstore->get('cons_tids_for_registration');
      // Consultations are taxonomy terms, use the protected func.
      $cons_list = $this->itemListFromTerms(
        $cons_items,
        $this->t('Consultations')
      );
    }

    if (!empty($food_list) || !empty($alert_list) || !empty($news_list) || !empty($cons_list)) {
      $content = [
        $food_list,
        $alert_list,
        $news_list,
        $cons_list,
      ];
    }
    else {
      $content = ['#markup' => '<p class="empty">' . $this->t('No subscriptions added yet.') . '</p>'];
    }

    return $content;

  }

  /**
   * List of subscribed items.
   *
   * @param array $terms
   *   Array of term id's.
   * @param string $title
   *   The title of the list.
   *
   * @return array
   *   List of subscribed terms.
   */
  protected function itemListFromTerms($terms, $title) {

    $items = [];
    foreach ($terms as $value) {
      if ($term = Term::load($value)) {
        $name = $term->getName();
        $items[] = ['#markup' => $name];
      }
      elseif ($value == 'all') {
        $items = ['#markup' => $title];
      }
    }

    if (!empty($items)) {
      return [
        '#theme' => 'item_list',
        '#list_type' => 'ul',
        '#title' => $title,
        '#attributes' => [
          'class' => [
            'item-selected',
          ],
        ],
        '#items' => $items,
      ];
    }
    else {
      return [];
    }
  }

}
