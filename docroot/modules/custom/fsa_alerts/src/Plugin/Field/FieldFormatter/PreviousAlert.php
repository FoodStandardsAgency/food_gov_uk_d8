<?php

namespace Drupal\fsa_alerts\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\Plugin\Field\FieldFormatter\BasicStringFormatter;
use Drupal\Core\Link;
use Drupal\node\NodeInterface;

/**
 * Plugin implementation of the 'fsa_previous_alert' formatter.
 *
 * Displays an alert notation value as a link to respective node.
 *
 * @FieldFormatter(
 *   id = "fsa_previous_alert",
 *   label = @Translation("Previous alert link"),
 *   field_types = {
 *     "string",
 *   }
 * )
 */
class PreviousAlert extends BasicStringFormatter {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    foreach ($items as $delta => $item) {
      $link = FALSE;

      // Get nodes that match with the notation ID. Although not by system but
      // this field value is unique so should always return just one node
      // object.
      $nodes = \Drupal::entityTypeManager()
        ->getStorage('node')
        ->loadByProperties(['field_alert_notation' => $item->value]);

      foreach ($nodes as $node) {
        if ($node instanceof NodeInterface) {
          $link = Link::createFromRoute($item->value . ': ' . $node->label(), 'entity.node.canonical', ['node' => $node->id()]);
        }
      }

      // In case there was no match to a node just display the field value.
      $value = ($link) ? $link : $item->value;

      $elements[$delta] = [
        '#type' => 'inline_template',
        '#template' => '<div class="field">{{ value|nl2br }}</div>',
        '#context' => ['value' => $value],
      ];

    }

    return $elements;
  }

}
