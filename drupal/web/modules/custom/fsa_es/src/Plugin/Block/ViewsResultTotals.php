<?php

namespace Drupal\fsa_es\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a views result totals block.
 *
 * @Block(
 *   id = "views_result_totals",
 *   admin_label = @Translation("Views result totals")
 * )
 */
class ViewsResultTotals extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    return [
      '#type' => 'container',
      '#attributes' => [
        'class' => [
          'views-result-total',
        ],
      ],
    ];
  }

}
