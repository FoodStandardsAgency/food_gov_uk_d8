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

      $vocab = \Drupal::entityTypeManager()
        ->getStorage('taxonomy_term')
        ->loadTree('topic', 0, 3);
      $name_first_chars = [];
      foreach ($vocab as $term) {
        $name_first_chars[] = strtoupper(substr($term->name, 0, 1));
      }
      $chars = array_unique($name_first_chars);
      sort($chars);

      // Generate markup.
      $output = '';
      $alphabet = range('A', 'Z');

      // Append any non-letters.
      foreach ($chars as $char) {
        if (!in_array($char, $alphabet)) {
          $output .= '<a href="#' . $char . '">' . $char . '</a>';
        }
      }

      // Append letters.
      foreach ($alphabet as $letter) {
        if (in_array($letter, $chars)) {
          $output .= '<a href="#' . strtolower($letter) . '">' . $letter . '</a>';
        }
        else {
          $output .= '<span>' . $letter . '</span>';
        }
      }
      return ['#markup' => $output];
    }
    return [];
  }

}
