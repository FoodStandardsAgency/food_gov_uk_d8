<?php

namespace Drupal\fsa_signin\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\fsa_signin\Controller\DefaultController;

/**
 * Provides a 'AlertSubscribeNav' block.
 *
 * @Block(
 *  id = "alert_subscribe_nav",
 *  admin_label = @Translation("Alerts subscribe navigation"),
 * )
 */
class AlertSubscribeNav extends BlockBase {

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

    $delivery_title = $this->t('Delivery options');
    if (
      $tempstore->get('alert_tids_for_registration') != NULL ||
      $tempstore->get('food_alert_registration') != NULL ||
      $tempstore->get('news_tids_for_registration') != NULL) {
      $delivery_page = DefaultController::linkMarkup('fsa_signin.user_registration_form', $delivery_title);
    }
    else {
      $delivery_page = $delivery_title;
    }

    $items = [
      ['#markup' => DefaultController::linkMarkup('fsa_signin.user_preregistration_alerts_form', $this->t('Food and allergy alerts'))],
      ['#markup' => DefaultController::linkMarkup('fsa_signin.user_preregistration_news_form', $this->t('News and consultations'))],
      ['#markup' => $delivery_page],
    ];


    // Build the menu as item_list.
    $build = [
      '#theme' => 'item_list',
      '#list_type' => 'ul',
      '#attributes' => [
        'class' => [
          'menu',
          'menu-subscribe',
        ],
      ],
      '#items' => $items,
    ];

    return $build;
  }

}
