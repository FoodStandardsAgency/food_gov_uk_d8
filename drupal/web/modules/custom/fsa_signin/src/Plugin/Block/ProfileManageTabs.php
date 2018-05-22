<?php

namespace Drupal\fsa_signin\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\fsa_signin\Controller\DefaultController;

/**
 * Provides a 'ProfileManageTabs' block.
 *
 * Display the tab-navigation for profile manage pages.
 *
 * @Block(
 *  id = "profile_manage_tabs",
 *  admin_label = @Translation("Profile manage tabs"),
 * )
 */
class ProfileManageTabs extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];

    // Disable block cache.
    // @todo: do not disable the cache :)
    $build['#cache'] = ['max-age' => 0];

    $items = [
      ['#markup' => DefaultController::linkMarkup('fsa_signin.user_preregistration_alerts_form', $this->t('Food and allergy alerts'))],
      ['#markup' => DefaultController::linkMarkup('fsa_signin.user_preregistration_news_form', $this->t('News and consultations'))],
      ['#markup' => DefaultController::linkMarkup('fsa_signin.default_controller_manageProfilePage', $this->t('Delivery options'))],
    ];

    // Build the menu as item_list.
    $build['content'] = [
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