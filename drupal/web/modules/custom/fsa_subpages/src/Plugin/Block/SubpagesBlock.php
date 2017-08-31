<?php

namespace Drupal\fsa_subpages\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\node\Entity\Node;

/**
 * Provides a 'SubpagesBlock' Block.
 *
 * @Block(
 *   id = "subpages_block",
 *   admin_label = @Translation("Sub-pages block"),
 *   category = @Translation("Hello World"),
 * )
 */
class SubpagesBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {

    // show block only on node pages
    $route = \Drupal::routeMatch()->getRouteName();
    if ($route != 'entity.node.canonical') {
      return [];
    }

    // make sure we have the node
    $node = \Drupal::routeMatch()->getParameter('node');
    if (empty($node)) {
      return [];
    }

    $nid = $node->id();
    // Reload the node to be sure it is correct type
    $node = Node::load($nid);
    $paragraphs = $node->get('field_subpages')->referencedEntities();
    $route = 'entity.node.canonical';
    $page = 1;
    $subpages = [];
    foreach ($paragraphs as $p) {
      $params = ['node' => $nid];
      $options = ['query' => ['subpage' => $page++]];
      $url = Url::fromRoute($route, $params, $options);
      $title = $p->get('field_title')->getString();
      $link = Link::fromTextAndUrl($title, $url);
      $render = $link->toRenderable();
      $subpages[] = $render;
    }

    // if there are no subpages
    // return minimum array
    // otherwise it will be cached
    if (empty($subpages)) {
      return [
        '#cache' => [
          'tags' => [
            "node:$nid",
          ],
        ],
      ];
    }

    return [
      '#theme' => 'item_list',
      '#list_type' => 'ul',
      '#items' => $subpages,
      '#attributes' => ['class' => ['subpages']],
      '#cache' => [
        'tags' => [
          "node:$nid",
        ],
        'contexts' => [
          'url.query_args:subpage',
        ],
      ],
    ];

  }

}
