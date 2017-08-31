<?php

namespace Drupal\fsa_document_library\Plugin\Field\FieldFormatter;

use Drupal\Component\Utility\Html;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\file\Plugin\Field\FieldFormatter\FileFormatterBase;

/**
 * Plugin implementation to style a document download link.
 *
 * @FieldFormatter(
 *   id = "fsa_document_mime_type_formatter",
 *   label = @Translation("Document with filetype"),
 *   field_types = {
 *      "file"
 *   }
 * )
 */
class DocumentWithMimeTypeFormatter extends FileFormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];
    $url = NULL;
    $files = $this->getEntitiesToView($items, $langcode);

    //    $download_link_setting = $this->getSetting(download_link_setting’);

    // Loop through the file entities.
    foreach ($files as $delta => $file) {

      $file_uri = $file->getFileUri();
      $url = Url::fromUri(file_create_url($file_uri));

      $link = Link::fromTextAndUrl(t('Download'), $url);
      // Set mimetype as html class.
      $attributes['class'] = 'type__' . Html::cleanCssIdentifier($file->getMimeType());

      $elements[$delta] = [
        '#theme' => 'fsa_file_download',
        '#attributes' => $attributes,
        '#filename' => $file->getFilename(),
        '#url' => $url,
        '#link' => $link,
        '#mimetype' => $file->getMimeType(),
        '#filesize' => format_size($file->getSize()),
      ];
    }

    return $elements;
  }

}
