<?php

namespace Drupal\fsa_subpages\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Url;
use Drupal\node\Entity\Node;

/**
 * Provides a 'PrevNextBlock' Block.
 *
 * @Block(
 *   id = "subpages_prevnext_block",
 *   admin_label = @Translation("Sub-pages prev-next block"),
 *   category = @Translation("Custom"),
 * )
 */
class SubpagesPrevNextBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {

    // Show block only on node pages.
    $route = \Drupal::routeMatch()->getRouteName();
    if ($route != 'entity.node.canonical') {
      return [];
    }

    // Make sure we have the node.
    $node = \Drupal::routeMatch()->getParameter('node');
    if (empty($node)) {
      return [];
    }

    $nid = $node->id();
    // Reload the node to be sure it is correct type.
    $node = Node::load($nid);
    if (!$node->hasField('field_subpages')) {
      return [];
    }
    $paragraphs = $node->get('field_subpages')->referencedEntities();

    $empty = [
      '#cache' => [
        'tags' => [
          "node:$nid",
        ],
      ],
    ];

    if (empty($paragraphs)) {
      return $empty;
    }

    // Build a list of valid sub-page aliases.
    $aliases = [];
    $index_min = 1;
    $index = $index_min;
    foreach ($paragraphs as $p) {
      $alias = $p->get('field_url_alias')->getString();
      $aliases[$alias] = [
        'index' => $index,
        'paragraph' => $p,
      ];
      $subpages[$index] = [
        'alias' => $alias,
        'paragraph' => $p,
      ];
      $index++;
    }
    $index_max = $index - 1;

    // Get URL query key-value pairs.
    $param = \Drupal::request()->query->all();

    // Check if query contains something that matches any sub-page alias.
    $match = array_intersect_key($aliases, $param);
    if (empty($match) || count($match) != 1) {
      return $empty;
    }

    // Current index.
    $index = reset($match)['index'];

    $nav = [
      '#attributes' => ['class' => ['prev-next']],
      '#cache' => [
        'tags' => [
          "node:$nid",
        ],
        'contexts' => [
          'url.query_args',
        ],
      ],
    ];

    if ($index > $index_min) {
      $prev = $subpages[$index - 1];
      $paragraph = $prev['paragraph'];
      $text = t('Previous');
      $class = 'prev block-link';
      $link = $this->direction($paragraph, $text, $nid, $class);
      $nav['prev'] = $link;
    }

    if ($index < $index_max) {
      $prev = $subpages[$index + 1];
      $paragraph = $prev['paragraph'];
      $text = t('Next');
      $class = 'next block-link';
      $link = $this->direction($paragraph, $text, $nid, $class);
      $nav['next'] = $link;
    }

    return $nav;

  }

  /**
   * Prev/next link.
   *
   * @param object $paragraph
   *   Paragraph entity.
   * @param string $text
   *   Link text.
   * @param int $nid
   *   Node entity id.
   * @param string $class
   *   HTML class.
   *
   * @return array
   *   The link array.
   */
  private function direction($paragraph, $text, $nid, $class) {

    $arrow = [
      '#type' => 'container',
      '#attributes' => ['class' => ['arrow']],
    ];

    $text = [
      '#markup' => $text,
    ];

    $direction = [
      '#type' => 'container',
      '#attributes' => ['class' => ['direction']],
      'arrow' => $arrow,
      'text' => $text,
    ];

    $title = $paragraph->get('field_title')->getString();
    $title = [
      '#markup' => $title,
    ];
    $title = [
      '#type' => 'container',
      '#attributes' => ['class' => ['title']],
      'text' => $title,
    ];

    $route = 'entity.node.canonical';
    $params = ['node' => $nid];
    $alias = $paragraph->get('field_url_alias')->getString();
    $options = ['query' => [$alias => NULL]];
    $url = Url::fromRoute($route, $params, $options);
    $link = [
      '#type' => 'link',
      '#attributes' => ['class' => [$class]],
      '#url' => $url,
      '#title' => [
        'direction' => $direction,
        'title' => $title,
      ],
    ];

    return $link;

  }

}
