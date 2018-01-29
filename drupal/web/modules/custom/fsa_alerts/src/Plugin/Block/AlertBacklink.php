<?php

namespace Drupal\fsa_alerts\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Link;
use Drupal\Core\Url;

/**
 * Provides a 'AlertBacklink' block.
 *
 * Conditionally displays a link back to news and alerts search tab of
 * corresponding content type.
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

    $route_provider = \Drupal::service('router.route_provider');
    $node = \Drupal::routeMatch()->getParameter('node');
    $type = $node->getType();

    $links = [
      'alert' => [
        'text' => t('All alerts'),
        'route_name' => 'view.search_news_alerts_alerts.page_1',
      ],
      'news' => [
        'text' => t('All news'),
        'route_name' => 'view.search_news_alerts_news.page_1',
      ],
      'consultatioan' => [
        'text' => t('All consultations'),
        'route_name' => 'view.search_news_alerts_consultations.page_1',
      ],
      'default' => [
        'text' => t('All news and alerts'),
        'route_name' => 'view.search_news_alerts_all.page_1',
      ],
    ];
    if (array_key_exists($type, $links) && count($route_provider->getRoutesByNames([$links[$type]['route_name']])) === 1) {
      $link = Link::createFromRoute($links[$type]['text'], $links[$type]['route_name'], [], ['attributes' => ['class' => 'back']]);
    }
    else {
      $link = Link::createFromRoute($links['default']['text'], $links['default']['route_name'], [], ['attributes' => ['class' => 'back']]);
    }

    $build['backlink'] = [
      '#markup' => $link->toString(),
    ];

    return $build;
  }

}
