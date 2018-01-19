<?php

namespace Drupal\fsa_custom\Plugin\views\area;

use Drupal\Core\Link;
use Drupal\views\Plugin\views\area\AreaPluginBase;
use Drupal\Core\Url;

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
      $items = [];
      $alphabet = range('A', 'Z');
      $view_path = Url::fromRoute('view.a_to_z.page_1')->toString();

      // Append any non-letters.
      foreach ($chars as $char) {
        if (!in_array($char, $alphabet)) {
          $item = Link::fromTextAndUrl($char, Url::fromUserInput($view_path . '/' . strtolower($char)))->toString();
          $items[] = [
            '#markup' => $item,
            '#wrapper_attributes' => [
              'class' => [
                'letter char',
              ],
            ],
          ];
        }
      }

      // Append letters.
      foreach ($alphabet as $letter) {
        $active = FALSE;
        if (in_array($letter, $chars)) {
          $item = Link::fromTextAndUrl($letter, Url::fromUserInput($view_path . '/' . strtolower($letter)))->toString();
          $empty = FALSE;

          // Figure out if the link is active.
          $path_basename = basename($_SERVER['REQUEST_URI']);
          if ($path_basename == strtolower($letter) || '/' . $path_basename == $view_path) {
            $active = 'is-active';
          }

        }
        else {
          // Do not create as link if no results.
          $item = '<span>' . $letter . '</span>';
          $empty = 'no-results';
        }

        $items[] = [
          '#markup' => $item,
          '#wrapper_attributes' => [
            'class' => [
              'topics__letter',
              $active,
              $empty,
            ],
          ],
        ];

      }

      // Return the "menu" list.
      return [
        '#theme' => 'item_list',
        '#list_type' => 'ul',
        '#attributes' => [
          'class' => [
            'topics__header',
          ],
        ],
        '#items' => $items,
      ];

    }

    return [];
  }

}
