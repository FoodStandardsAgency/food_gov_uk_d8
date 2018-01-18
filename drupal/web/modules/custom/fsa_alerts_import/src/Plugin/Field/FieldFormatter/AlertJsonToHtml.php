<?php

namespace Drupal\fsa_alerts_import\Plugin\Field\FieldFormatter;

use Drupal\Component\Utility\Html;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;

/**
 * Plugin implementation of 'fsa_alert_json_formatter'.
 *
 * Formats raw FSA Alert Json data to html presentation.
 *
 * @FieldFormatter(
 *   id = "fsa_alert_json_formatter",
 *   label = @Translation("FSA Alert Json to HTML"),
 *   field_types = {
 *      "string_long"
 *   }
 * )
 */
class AlertJsonToHtml extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary[] = $this->t('Render FSA Alerts API raw Json as HTML');

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    // Currently built for API product details only.
    foreach ($items as $delta => $item) {

      $data = json_decode($item->value, TRUE);

      if (!is_array($data) || json_last_error() != JSON_ERROR_NONE) {
        drupal_set_message('Failed formatting the product details.', 'warning');
        return $elements;
      }

      // Loop through each product entry.
      foreach ($data as $key => $value) {
        $table_caption = '';
        $table_rows = [];

        if (isset($value['productName'])) {
          $table_caption = $this->itemWrapper(FALSE, nl2br($value['productName']), 'p');
        }

        // Optional pack size description.
        if (isset($value['packSizeDescription'])) {
          $table_rows[] = [t('Pack size'), $value['packSizeDescription']];
        }

        // Optional product code(s).
        if (isset($value['productCodes'])) {
          $table_rows[] = [t('Product code'), $value['productCodes']];
        }

        if (isset($value['batchDescription'])) {
          // Loop through batch descriptions and get only content from keys we
          // care about.
          foreach ($value['batchDescription'] as $b_key => $b_value) {
            if (isset($b_value['batchCode'])) {
              $table_rows[] = [t('Batch code'), $b_value['batchCode']];
            }
            if (isset($b_value['bestBeforeDate'])) {
              $table_rows[] = [t('Best before date'), $b_value['bestBeforeDate']];
            }
            if (isset($b_value['bestBeforeDescription'])) {
              $table_rows[] = [t('Best before description'), $b_value['bestBeforeDescription']];
            }
            if (isset($b_value['useByDate'])) {
              $table_rows[] = [t('Use by date'), $b_value['useByDate']];
            }
            if (isset($b_value['useByDate'])) {
              $table_rows[] = [t('Use by description'), $b_value['useByDescription']];
            }
          }
        }

        $elements[$key] = [
          '#theme' => 'table',
          '#caption' => $table_caption,
          '#header' => NULL,
          '#rows' => $table_rows,
        ];
      }
    }

    return $elements;
  }

  /**
   * Generate the output appropriate for single field item.
   *
   * @param \Drupal\Core\Field\FieldItemInterface $item
   *   One field item.
   *
   * @return string
   *   The textual output generated.
   */
  protected function viewValue(FieldItemInterface $item) {
    return nl2br(Html::escape($item->value));
  }

  /**
   * Product detail label.
   *
   * @param string $label
   *   The label as a string.
   * @param mixed $options
   *   Label options.
   *
   * @return string
   *   Label with separator and html tag.
   */
  protected function labelWrapper($label, $options = FALSE) {
    if (!$options) {
      $options = [
        'tag' => 'b',
        'separator' => ': ',
      ];
    }
    return '<' . $options['tag'] . '>' . $label . $options['separator'] . '</' . $options['tag'] . '> ';
  }

  /**
   * Product detail item wrapper.
   *
   * @param string $label
   *   The label.
   * @param string $content
   *   The item.
   * @param string $tag
   *   The tag to wrap the label and item.
   * @param array $label_options
   *   Options to be sent for label.
   *
   * @return array
   *   Markup array of wrapped product detail.
   */
  protected function itemWrapper($label, $content, $tag = 'div', $label_options = ['tag' => 'b', 'separator' => ': ']) {
    if (!$label) {
      $markup = '<' . $tag . '>' . $content . '</' . $tag . '>';
    }
    else {
      $markup = '<' . $tag . '>' . $this->labelWrapper($label, $label_options) . $content . '</' . $tag . '>';
    }
    return ['#markup' => $markup];
  }

}
