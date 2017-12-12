<?php

namespace Drupal\fsa_signin\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\fsa_signin\Controller\DefaultController;

/**
 * Provides 'Manage your subscription' CTA link/block.
 *
 * @Block(
 *  id = "manage_subscription_cta",
 *  admin_label = @Translation("Manage subscription CTA"),
 * )
 */
class ManageSubscriptionCta extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $content = DefaultController::linkMarkup('fsa_signin.default_controller_signInPage', $this->t('Manage your subscription'), ['gear icon']);

    return ['#markup' => $content];

  }

}
