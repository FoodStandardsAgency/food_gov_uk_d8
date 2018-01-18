<?php

namespace Drupal\fsa_custom\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\Plugin\Field\FieldFormatter\EntityReferenceFormatterBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation to style regional variation.
 *
 * @FieldFormatter(
 *   id = "fsa_regional_variation",
 *   label = @Translation("FSA Regional variation"),
 *   field_types = {
 *      "entity_reference"
 *   }
 * )
 */
class RegionalVariationFormatter extends EntityReferenceFormatterBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'style' => TRUE,
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $elements['style'] = [
      '#type' => 'select',
      '#title' => $this->t('Formatting style'),
      '#options' => [
        'attachment' => $this->t('Attachment label style'),
        'page_label' => $this->t('Page label style'),
      ],
      '#default_value' => $this->getSetting('style'),
    ];

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];

    switch ($this->getSetting('style')) {
      case 'attachment':
        $summary[] = t('Attachment label style');
        break;

      case 'page_label':
        $summary[] = t('Page label style');
        break;

      default:
        $summary[] = t('No style');
        break;
    }
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];
    $style = $this->getSetting('style');

    if (count($items) >= 0 && count($items) < 3) {
      foreach ($this->getEntitiesToView($items, $langcode) as $delta => $entity) {
        $label = $entity->label();

        // Attachment (short) formatting.
        if ($style == 'attachment') {
          $elements[$delta] = ['#plain_text' => 'Shortened style: ' . $label];
        }
        elseif ($style == 'page_label') {
          $elements[$delta] = ['#plain_text' => 'Longer style: ' . $label];
        }

        $elements[$delta]['#cache']['tags'] = $entity->getCacheTags();
      }
    }

    return $elements;

  }

}
