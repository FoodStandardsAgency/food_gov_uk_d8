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

      $data = json_decode($item->value,true);

      foreach ($data AS $key => $value) {

        // Set only product name
        // @todo: print out everything needed as in http://fsa-staging-alerts.epimorphics.net/food-alerts/ui/reference#alerts-product
        $products[] = $value['productName'];

        switch ($key) {
          case 'productName':

            break;
          case 'productCodes':

            break;
        }

        // Batch description.
        if ($key == 'batchDescription') {
          foreach ($value AS $b_key => $b_value) {

            switch ($b_key) {
              case 'productCodes':
                // Batch description affected product codes.
                break;
              case 'productName':
                // Batch description affected product names.
                break;
              case 'batchDescription':
                // Batch descriptions (for BBE dates).
                break;
              case 'packSizeDescription':
                // Pack size desc.
                break;
              default:
                continue;
            }
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
}
