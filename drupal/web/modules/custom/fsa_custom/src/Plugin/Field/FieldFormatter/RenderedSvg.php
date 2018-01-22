<?php

namespace Drupal\fsa_custom\Plugin\Field\FieldFormatter;

use Drupal\file\Plugin\Field\FieldFormatter\FileFormatterBase;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Component\Utility\Html;
use Drupal\Core\Url;
use Drupal\Core\Link;

/**
 * Plugin implementation to display SVG path as rendered image.
 *
 * @FieldFormatter(
 *   id = "fsa_rendered_svg",
 *   label = @Translation("Rendered SVG"),
 *   field_types = {
 *      "file"
 *   }
 * )
 */
class RenderedSvg extends FileFormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];
    $files = $this->getEntitiesToView($items, $langcode);

    // Loop the files and send path to img tag.
    foreach ($files as $delta => $file) {
      $elements[$delta] = [
        '#theme' => 'image',
        '#uri' => $file->getFileUri(),
        '#alt' => $this->t('Icon'),
      ];
    }

    return $elements;
  }

}
