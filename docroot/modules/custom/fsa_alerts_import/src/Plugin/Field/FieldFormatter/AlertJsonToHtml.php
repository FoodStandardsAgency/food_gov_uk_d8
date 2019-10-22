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
        drupal_set_message($this->t('Failed formatting the product details.'), 'warning');
        return $elements;
      }

      // Loop through each product entry.
      foreach ($data as $key => $value) {
        $table_caption = '';
        $table_rows = [];

        if (isset($value['productName'])) {
          $table_caption = $this->itemWrapper(FALSE, nl2br($value['productName']), 'p');
        }

        // Optional product code(s).
        if (isset($value['productCode'])) {
          $table_rows[] = [t('Product code'), $value['productCode']];
        }

        // Optional pack size description.
        if (isset($value['packSizeDescription'])) {
          $table_rows[] = [t('Pack size'), $value['packSizeDescription']];
        }

        if (isset($value['batchDescription'])) {
          // Loop through batch descriptions and get only content from keys we
          // care about. Define content we want mapped with translatable labels.
          $batch_fields = [
            'batchCode' => t('Batch code'),
            'lotNumber' => t('Lot number'),
            //'bestBeforeDate' => t('Best before date'),
            'useByDescription' => t('Use by'),
            'bestBeforeDescription' => t('Best before'),
            'batchTextDescription' => t('Batch description'),
            //'useByDate' => t('Use by date'),
          ];
          foreach ($value['batchDescription'] as $b_key => $b_value) {
            foreach ($batch_fields as $batch_field_key => $batch_field_value) {
              if (isset($b_value[$batch_field_key])) {
                $table_rows[] = [$batch_field_value, $b_value[$batch_field_key]];
              }
            }
          }
        }

        // Loop through allergen data and narrow down to further definitions if
        // available within JSON.
        if (isset($value['allergen'])) {
          $cell_label = t('Allergens');
          $allergens = [];

          foreach ($value['allergen'] as $allergen) {
            // Top level allergens which have no associated parents or children.
            if (!isset($allergen['broader']) && !isset($allergen['narrower'])) {
              $allergens[] = $allergen['label'];
            }
            // Top level allergens which have associated children.
            elseif (!isset($allergen['broader']) && isset($allergen['narrower'])) {
              $label = $allergen['label'];

              // Loop through children and check if label is available.
              $children = [];
              foreach ($allergen['narrower'] as $child) {
                if (isset($child['label'])) {
                  $children[] = strtolower($child['label']);
                }
              }

              // Build comma delimited list of child allergens within brackets.
              if (!empty($children)) {
                $children = implode(', ', $children);
                $label .= ' (' . $children . ')';
              }

              $allergens[] = $label;
            }
          }

          // Build comma delimited list of allergens and their children.
          $allergens = implode(', ', $allergens);
          $table_rows[] = [$cell_label, $allergens];
        }

        $elements[$key] = [
          '#theme' => 'table',
          '#caption' => $table_caption,
          '#header' => NULL,
          '#rows' => $table_rows,
        ];
      }
    }

    // Check if the entity has a reporting business field with value and append
    // additional information at the bottom of product details.
    if ($items->getEntity()->hasField('field_alert_reportingbusiness')) {
      // Field is multi-value but API always have only one value (delta)
      $reporting_business = $items->getEntity()->get('field_alert_reportingbusiness')->getValue();
      if (isset($reporting_business[0]['value'])) {
        $elements[] = [
          '#markup' => '<p class="disclaimer">' . $this->t(
            'No other @reporting_business products are known to be affected.',
            ['@reporting_business' => $reporting_business[0]['value']]
          ) . '</p>',
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
  protected function itemWrapper($label, $content, $tag = 'div', array $label_options = ['tag' => 'b', 'separator' => ': ']) {
    if (!$label) {
      $markup = '<' . $tag . '>' . $content . '</' . $tag . '>';
    }
    else {
      $markup = '<' . $tag . '>' . $this->labelWrapper($label, $label_options) . $content . '</' . $tag . '>';
    }
    return ['#markup' => $markup];
  }

}
