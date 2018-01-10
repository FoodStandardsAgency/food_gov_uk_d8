<?php

namespace Drupal\fsa_alerts\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Link;
use Drupal\Core\Url;

/**
 * Provides a 'AlertBacklink' block.
 *
 * @Block(
 *  id = "alert_backlink",
 *  admin_label = @Translation("News & alerts search tab backlink"),
 * )
 */
class AlertBacklink extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];

    $node = \Drupal::routeMatch()->getParameter('node');
    $type = $node->getType();

    // Set path per content type.
    // @todo: get from route once searh tabs are added to setup.
    switch ($type) {
      case 'alert':
        $path = '/news-alerts/alerts';
        break;

      case 'news':
        $path = '/news-alerts/news';
        break;

      case 'consultation':
        $path = '/news-alerts/consultations';
        break;

      default:
        $path = '/news-alerts/all';
        break;

    }

    // Classes to theme it.
    $options = ['attributes' => ['class' => 'arrow-back']];

    $url = Url::fromUserInput($path, $options);

    // Link to News & Alerts listing page.
    $build['backlink'] = [
      '#markup' => Link::fromTextAndUrl($this->t('News and alerts'), $url)->toString(),
    ];

    return $build;
  }

}
