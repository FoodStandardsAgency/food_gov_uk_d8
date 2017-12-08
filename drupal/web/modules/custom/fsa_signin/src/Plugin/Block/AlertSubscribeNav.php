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

    $items = [
      ['#markup' => DefaultController::linkMarkup('fsa_signin.user_preregistration_alerts_form', $this->t('Food and allergy alerts'))],
      ['#markup' => DefaultController::linkMarkup('fsa_signin.user_preregistration_news_form', $this->t('News and consultations'))],
      ['#markup' => DefaultController::linkMarkup('fsa_signin.user_registration_form', $this->t('Delivery options'))],
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
