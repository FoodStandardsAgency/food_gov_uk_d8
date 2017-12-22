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

      // Get first letter of names, ordered, and without duplicates.
      $query = \Drupal::entityQuery('taxonomy_term');
      $query->condition('vid', "topic");
      $tids = $query->execute();
      $terms = \Drupal::entityTypeManager()
        ->getStorage('taxonomy_term')
        ->loadMultiple($tids);
      $name_first_chars = [];
      foreach ($terms as $term) {
        if ($name = $term->getName()) {
          $name_first_chars[] = strtoupper(substr($name, 0, 1));
        }
      }
      $chars = array_unique($name_first_chars);
      sort($chars);

      // Generate markup.
      $output = '';
      $alphabet = range('A', 'Z');

      // Add any non-letters.
      foreach ($chars as $char) {
        if (!in_array($char, $alphabet)) {
          $output .= '<a href="#' . $char . '">' . $char . '</a>';
        }
      }

      // Add letters.
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
