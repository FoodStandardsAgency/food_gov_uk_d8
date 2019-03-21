<?php

namespace Drupal\fsa_custom\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides the back to top link at the bottom of pages.
 *
 * @Block(
 *   id = "fsa_back_to_top_block",
 *   admin_label = @Translation("FSA back to top link block"),
 *   category = @Translation("FSA"),
 * )
 */
class FSABackToTopLink extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {

    return [
      '#markup' => '<div><a class="back-to-top" href="#">' . t('Back to top') . '</a></div>',
    ];
  }

}
