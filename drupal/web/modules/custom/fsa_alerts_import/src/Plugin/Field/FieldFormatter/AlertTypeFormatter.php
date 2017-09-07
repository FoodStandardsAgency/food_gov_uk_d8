<?php

namespace Drupal\fsa_alerts_import\Plugin\Field\FieldFormatter;

use Drupal\Component\Utility\Html;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;

/**
 * Plugin implementation of the 'fsa_score_description_formatter' formatter.
 *
 * Formats FHRS API Scores to textual representation.
 * repository.
 *
 * @FieldFormatter(
 *   id = "fsa_alert_type_formatter",
 *   label = @Translation("FSA Food Alert type"),
 *   field_types = {
 *      "list_string"
 *   }
 * )
 */
class AlertTypeFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary[] = $this->t('Display themed, human readable format of Alert type.');

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    foreach ($items as $delta => $item) {

      $attributes['class'] = 'alert__type_' . strtolower(Html::cleanCssIdentifier($this->viewValue($item)));

      $elements[$delta] = [
        '#theme' => 'fsa_alert_type',
        '#attributes' => $attributes,
        '#title' => $this->fsaAlertTypeTitle($this->viewValue($item)),
        '#type' => $this->viewValue($item),
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
   * FSA Ratings score title.
   *
   * @param string $type
   *   The allergy type.
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup|string
   *   Human readable, translated allergy type.
   */
  protected function fsaAlertTypeTitle($type) {

    switch ($type) {
      case 'AA':
        $content = $this->t('Allergy Alert');
        break;

      case 'PRIN':
        $content = $this->t('Product Recall Information Notice');
        break;

      case 'FAFA':
        $content = $this->t('Food Alert for Action');
        break;

      default:
        $content = $this->t('Unclassified alert');
        break;
    }
    return $content;
  }


}
