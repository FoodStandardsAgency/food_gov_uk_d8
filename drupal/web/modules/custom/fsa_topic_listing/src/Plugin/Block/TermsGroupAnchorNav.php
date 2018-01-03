<?php

namespace Drupal\fsa_topic_listing\Plugin\Block;

use Drupal\Component\Utility\Html;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Link;
use Drupal\Core\Url;

/**
 * Creates 'Term group nav' for taxonomy listing page.
 *
 * @Block(
 *  id = "terms_group_anchor_nav",
 *  admin_label = @Translation("Term group anchor nav"),
 * )
 */
class TermsGroupAnchorNav extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {

    // @todo: Get the groups programmatically.
    $groups = [
      'Business guidance',
      'Consumer guidance',
    ];

    $links = [];
    foreach ($groups as $group) {
      $anchor = '#' . strtolower(Html::cleanCssIdentifier($group));
      $url = Url::fromUserInput($anchor);
      $links[] = ['#markup' => Link::fromTextAndUrl($group, $url)->toString()];
    }

    // Build the anchor nav.
    return [
      '#theme' => 'item_list',
      '#list_type' => 'ul',
      '#wrapper_attributes' => [
        'class' => [
          'anchor-nav',
        ],
      ],
      '#items' => $links,
    ];
  }

}
