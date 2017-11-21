<?php

namespace Drupal\fsa_alerts\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Link;

/**
 * Provides a 'AlertBacklink' block.
 *
 * @Block(
 *  id = "alert_backlink",
 *  admin_label = @Translation("Alert page backlink"),
 * )
 */
class AlertBacklink extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];

    $options = ['attributes' => ['class' => 'arrow-back']];

    // Link to News & Alerts listing page.
    $build['backlink'] = [
      '#markup' => Link::createFromRoute($this->t('Back'), 'entity.node.canonical', ['node' => '56'], $options)->toString(),
    ];

    return $build;
  }

}
