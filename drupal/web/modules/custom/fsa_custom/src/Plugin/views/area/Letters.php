<?php

namespace Drupal\fsa_custom\Plugin\views\area;

use Drupal\views\Plugin\views\area\AreaPluginBase;

/**
 * Defines a views area handler for a-z letter anchors.
 *
 * @ingroup views_area_handlers
 *
 * @ViewsArea("letters")
 */
class Letters extends AreaPluginBase {

  /**
   * {@inheritdoc}
   */
  public function render($empty = FALSE) {
    if (!$empty || !empty($this->options['empty'])) {

      // Get first letter of titles, ordered, and without duplicates.
      $nids = \Drupal::entityQuery('node')->execute();
      $nodes = \Drupal::entityTypeManager()
        ->getStorage('node')
        ->loadMultiple($nids);
      $title_first_letters = [];
      foreach ($nodes as $node) {
        if ($title = $node->getTitle()) {
          $title_first_letters[] = substr($title, 0, 1);
        }
      }
      $letters = array_unique($title_first_letters);
      sort($letters);

      // Generate markup.
      $output = '';
      foreach ($letters as $letter) {
        $output .= '<a href="#' . strtolower($letter) . '">' . strtoupper($letter) . '</a>';
      }
      return ['#markup' => $output];
    }
    return [];
  }

}
