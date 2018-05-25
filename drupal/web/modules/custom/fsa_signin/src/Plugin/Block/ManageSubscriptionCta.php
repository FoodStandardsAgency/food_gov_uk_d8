<?php

namespace Drupal\fsa_signin\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Link;
use Drupal\Core\Url;
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
    $text = $this->t('Manage your subscription');
    // @todo: Add configuration options to enable/disable redirection.
    if (\Drupal::state()->get('fsa_signin.redirect')) {
      $uri = \Drupal::state()->get('fsa_signin.external_profile_manage_url');
      $url = Url::fromUri($uri);
      $url->setOptions(['attributes' => ['class' => ['gear icon']]]);
      $content = Link::fromTextAndUrl($text, $url)->toString();
    }
    elseif (\Drupal::currentUser()->isAuthenticated()) {
      $content = DefaultController::linkMarkup('fsa_signin.user_preregistration_alerts_form', $text, ['gear icon']);
      $content .= ' / ' . DefaultController::linkMarkup('user.logout.http', $this->t('Logout'), ['logout icon']);
    }
    else {
      $content = DefaultController::linkMarkup('fsa_signin.default_controller_signInPage', $text, ['gear icon']);
    }


    return ['#markup' => $content];

  }

}
