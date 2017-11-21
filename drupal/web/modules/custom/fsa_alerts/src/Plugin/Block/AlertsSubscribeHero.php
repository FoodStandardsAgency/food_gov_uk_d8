<?php

namespace Drupal\fsa_alerts\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\fsa_alerts\FsaAlertsHelper;

/**
 * Provides a 'AlertsSubscribeHero' block.
 *
 * @Block(
 *  id = "alerts_subscribe_hero",
 *  admin_label = @Translation("Alerts subscribe hero"),
 * )
 */
class AlertsSubscribeHero extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];

    $title = $this->t('Be updated');
    $copy = $this->t("Get email and SMS updates on latest news, food recalls and allergy alerts.");

    $cta = FsaAlertsHelper::ctaSubscribe();

    $build['alerts_subscribe_hero'] = [
      '#theme' => 'fsa_alerts_subscribe_hero',
      '#attributes' => NULL,
      '#title' => $title,
      '#copy' => $copy,
      '#cta' => $cta,
    ];

    return $build;
  }

}
