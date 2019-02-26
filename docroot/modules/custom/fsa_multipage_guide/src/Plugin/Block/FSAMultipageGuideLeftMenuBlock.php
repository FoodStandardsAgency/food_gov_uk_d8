<?php

namespace Drupal\fsa_multipage_guide\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\entityqueue\Entity\EntityQueue;

/**
 * Provides the Multipage guide left hand menu block
 *
 * @Block(
 *   id = "fsa_multipage_guide_left_hand_menu_block",
 *   admin_label = @Translation("FSA Multipage Guide Left Hand Menu block"),
 *   category = @Translation("FSA"),
 * )
 */
class FSAMultipageGuideLeftMenuBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {

    $node = \Drupal::routeMatch()->getParameter('node');
    if ($node instanceof \Drupal\node\NodeInterface && $node->getType() === '') {
      return array();
    }

    // Decide if this is a page that warrants a side menu.
  //  $queue =


    return array(
      '#markup' => $this->t('Hello, World!'),
    );
  }

}
