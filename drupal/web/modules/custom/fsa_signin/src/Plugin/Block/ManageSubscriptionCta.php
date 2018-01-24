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
    $classes = ['gear icon'];
    // @todo: Add configuration options to enable/disable redirection.
    if (TRUE) {
      // @todo: Add configuration option for the URL.
      $uri = 'https://public.govdelivery.com/accounts/UKFSA/subscriber/new';
      $url = Url::fromUri($uri);
      $url->setOptions(['attributes' => ['class' => $classes]]);
      $content = Link::fromTextAndUrl($text, $url)->toString();
    }
    else {
      $content = DefaultController::linkMarkup('fsa_signin.default_controller_signInPage', $text, $classes);
    }

    return ['#markup' => $content];

  }

}
