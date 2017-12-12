<?php

namespace Drupal\fsa_signin\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\fsa_signin\Controller\DefaultController;

/**
 * Provides 'New user link block' for subscribe CTA block.
 *
 * @Block(
 *  id = "new_user",
 *  admin_label = @Translation("New user link block"),
 * )
 */
class NewUser extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $content = '<p>' . $this->t('Subscribe to news and alerts.') . '</p>';
    $content .= DefaultController::linkMarkup('fsa_signin.user_preregistration', 'Subscribe', ['button']);

    return ['#markup' => $content];

  }

}
