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
      '#description' => $this->t('<strong>Attachment</strong> style displays only label(s)<br /><strong>Page label</strong> style displays label(s) and additional wording.'),
      '#options' => [
        'attachment' => $this->t('Attachment label'),
        'page_label' => $this->t('Page label'),
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
        $summary[] = t('Attachment label (short format)');
        break;

      case 'page_label':
        $summary[] = t('Page label (long format)');
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
    $cachetags = FALSE;
    $elements = [];
    $labels = [];
    $region = FALSE;
    $style = $this->getSetting('style');

    if (count($items) > 0 && count($items) < 3) {
      foreach ($this->getEntitiesToView($items, $langcode) as $delta => $entity) {
        $labels[] = $entity->label();
        $cachetags = $entity->getCacheTags();
      }

      if (count($labels) == 1) {
        // Only one label.
        if ($style == 'attachment') {
          // The short mode.
          $region = $labels[0];
        }
        elseif ($style == 'page_label') {
          // The long mode (above title style).
          $region = $this->t('@region specific', ['@region' => $labels[0]]);
        }
      }
      elseif (count($labels) == 2) {
        // When two regions assigned.
        if ($style == 'attachment') {
          // The short mode.
          $region = $this->t(
              '@first_region and @second_region', [
                '@first_region' => $labels[0],
                '@second_region' => $labels[1],
              ]);
        }
        elseif ($style == 'page_label') {
          // The long mode (above title style).
          $region = $this->t(
              '@first_region and @second_region specific', [
                '@first_region' => $labels[0],
                '@second_region' => $labels[1],
              ]);
        }
      }
      $elements[$delta] = ['#markup' => $region];
      $elements[$delta]['#cache']['tags'] = $cachetags;
    }
    return $elements;

  }

}
