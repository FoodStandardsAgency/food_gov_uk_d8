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
    $attributes = [];
    $products = [];

    foreach ($items as $delta => $item) {

      $data = json_decode($item->value, TRUE);

      if (json_last_error() != JSON_ERROR_NONE) {
        drupal_set_message('Malformatted product details json', 'warning');
        return $elements;
      }

      // Loop through each product entry.
      foreach ($data as $key => $value) {

        $products[]['productName'] = [
          '#markup' => $this->labelWrapper($value['productName'], 'h4', FALSE),
        ];

        if (isset($value['packSizeDescription'])) {
          $products[]['packSizeDescription'] = ['#markup' => $this->labelWrapper(t('Pack size')) . $value['packSizeDescription']];
        }

        if (isset($value['productCodes'])) {
          $products[]['productCode'] = [
            '#markup' => $this->labelWrapper(t('Product code')) . $value['productCodes'],
          ];
        }

        // Loop sub-arrays.
        foreach ($value as $b_key => $b_value) {
          // Print out only keys we care about.
          switch ($b_key) {
            case 'batchDescription':
              foreach ($b_value as $ba_item) {
                if (isset($ba_item['batchCode'])) {
                  $products[]['batchCode'] = ['#markup' => $this->labelWrapper(t('Batch code')) . $ba_item['batchCode']];
                }
                if (isset($ba_item['bestBeforeDate'])) {
                  $products[]['bestBeforeDate'] = ['#markup' => $this->labelWrapper(t('Best before date')) . $ba_item['bestBeforeDate']];
                }
                if (isset($ba_item['bestBeforeDescription'])) {
                  $products[]['bestBeforeDescription'] = ['#markup' => $this->labelWrapper(t('Best before description')) . $ba_item['bestBeforeDescription']];
                }
                if (isset($ba_item['useByDate'])) {
                  $products[]['useByDate'] = ['#markup' => $this->labelWrapper(t('Use by date')) . $ba_item['useByDate']];
                }
                if (isset($ba_item['useByDate'])) {
                  $products[]['useByDate'] = ['#markup' => $this->labelWrapper(t('Use by description')) . $ba_item['useByDescription']];
                }
              }
              break;
          }
        }
      }

      $elements[$delta] = [
        '#theme' => 'fsa_alert_product_details',
        '#attributes' => $attributes,
        '#products' => $products,
      ];
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
   * @param $label
   * @param string $tag
   * @param string $separator
   *
   * @return string
   */
  protected function labelWrapper($label, $tag = 'strong', $separator = ': ') {
    return '<' . $tag . '>' . $label . $separator . '</' . $tag . '> ';
  }

}
