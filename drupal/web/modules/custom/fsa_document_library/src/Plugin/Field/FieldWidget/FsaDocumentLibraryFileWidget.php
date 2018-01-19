<?php

namespace Drupal\fsa_document_library\Plugin\Field\FieldWidget;

use Drupal\Component\Utility\NestedArray;
use Drupal\file\Plugin\Field\FieldWidget\FileWidget;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;

/**
 * Plugin implementation of the 'file_generic' widget.
 *
 * @FieldWidget(
 *   id = "file_generic",
 *   label = @Translation("File"),
 *   field_types = {
 *     "file"
 *   }
 * )
 */
class FsaDocumentLibraryFileWidget extends FileWidget {

  /**
   * Form API callback: Processes a file_generic field element.
   *
   * Expands the file_generic type to include the description and display
   * fields.
   *
   * This method is assigned as a #process callback in formElement() method.
   */
  public static function process($element, FormStateInterface $form_state, $form) {
    $item = $element['#value'];
    $item['fids'] = $element['fids']['#value'];

    // Add the display field if enabled.
    if ($element['#display_field']) {
      $element['display'] = [
        '#type' => empty($item['fids']) ? 'hidden' : 'checkbox',
        '#title' => t('Include file in display'),
        '#attributes' => ['class' => ['file-display']],
      ];
      if (isset($item['display'])) {
        $element['display']['#value'] = $item['display'] ? '1' : '';
      }
      else {
        $element['display']['#value'] = $element['#display_default'];
      }
    }
    else {
      $element['display'] = [
        '#type' => 'hidden',
        '#value' => '1',
      ];
    }

    // Add the description field if enabled.
    if ($element['#description_field'] && $item['fids']) {
      $config = \Drupal::config('file.settings');
      $element['description'] = [
        '#type' => $config->get('description.type'),
        '#title' => t('File link title'),
        '#value' => isset($item['description']) ? $item['description'] : '',
        '#maxlength' => $config->get('description.length'),
        '#description' => t('File link title is displayed when documents are embedded into content pages. If not populated will display file name.'),
        '#required' => TRUE,
      ];
    }

    // Adjust the Ajax settings so that on upload and remove of any individual
    // file, the entire group of file fields is updated together.
    if ($element['#cardinality'] != 1) {
      $parents = array_slice($element['#array_parents'], 0, -1);
      $new_options = [
        'query' => [
          'element_parents' => implode('/', $parents),
        ],
      ];
      $field_element = NestedArray::getValue($form, $parents);
      $new_wrapper = $field_element['#id'] . '-ajax-wrapper';
      foreach (Element::children($element) as $key) {
        if (isset($element[$key]['#ajax'])) {
          $element[$key]['#ajax']['options'] = $new_options;
          $element[$key]['#ajax']['wrapper'] = $new_wrapper;
        }
      }
      unset($element['#prefix'], $element['#suffix']);
    }

    // Add another submit handler to the upload and remove buttons, to implement
    // functionality needed by the field widget. This submit handler, along with
    // the rebuild logic in file_field_widget_form() requires the entire field,
    // not just the individual item, to be valid.
    foreach (['upload_button', 'remove_button'] as $key) {
      $element[$key]['#submit'][] = [get_called_class(), 'submit'];
      $element[$key]['#limit_validation_errors'] = [
        array_slice($element['#parents'], 0, -1),
      ];
    }

    return $element;
  }

}
