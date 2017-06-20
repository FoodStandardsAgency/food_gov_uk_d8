<?php

namespace Drupal\fsa_ratings\Plugin\Field\FieldFormatter;

use Drupal\Component\Utility\Html;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'rating_key_formatter' formatter.
 *
 * Formats FHRS API RatingKey string as an image referenced from FSA image
 * repository.
 *
 * @FieldFormatter(
 *   id = "rating_key_formatter",
 *   label = @Translation("Rating key image formatter"),
 *   field_types = {
 *     "string"
 *   }
 * )
 */
class RatingKeyFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'image_size' => 'medium',
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    // The image sizes as in http://ratings.food.gov.uk/images/... would return.
    $element['image_size'] = [
      '#title' => t('Image size'),
      '#type' => 'select',
      '#options' => [
        'small' => $this->t('Small'),
        'medium' => $this->t('Medium'),
        'large' => $this->t('Large'),
      ],
      '#default_value' => $this->getSetting('image_size'),
    ] + parent::settingsForm($form, $form_state);;

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];
    // Implement settings summary.
    $summary[] = t('Format ratingKey string to an image referenced from ratings.food.gov.uk');

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    foreach ($items as $delta => $item) {
      $fieldValue = '<img src="http://ratings.food.gov.uk/images/scores/' . $this->getSetting('image_size') . '/' . $this->viewValue($item) . '.JPG" />';
      $elements[$delta] = ['#markup' => $fieldValue];
    }

    return $elements;
  }

  /**
   * Generate the output appropriate for one field item.
   *
   * @param \Drupal\Core\Field\FieldItemInterface $item
   *   One field item.
   *
   * @return string
   *   The textual output generated.
   */
  protected function viewValue(FieldItemInterface $item) {
    // The text value has no text format assigned to it, so the user input
    // should equal the output, including newlines.
    return nl2br(Html::escape($item->value));
  }

}
