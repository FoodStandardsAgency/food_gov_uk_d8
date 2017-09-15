<?php

namespace Drupal\fsa_alerts\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\fsa_alerts\FsaAlertsHelper;

/**
 * Provides a 'AlertsSubscribeCta' block.
 *
 * @Block(
 *  id = "alerts_subscribe_cta",
 *  admin_label = @Translation("Alerts subscribe CTA link"),
 * )
 */
class AlertsSubscribeCta extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];

    $cta = FsaAlertsHelper::ctaSubscribe()->toString();

    $build['alerts_subscribe_cta'] = [
      '#markup' => $cta,
    ];

    return $build;
  }

}
