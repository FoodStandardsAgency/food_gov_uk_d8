<?php

namespace Drupal\fsa_ratings\Plugin\Field\FieldFormatter;

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
 *   id = "fsa_score_description_formatter",
 *   label = @Translation("FSA Score description formatter"),
 *   field_types = {
 *      "integer"
 *   }
 * )
 */
class ScoreDescriptionFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary[] = t('Translates FHRS API score keys (integer) to human-readable format with description');

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    foreach ($items as $delta => $item) {
      $elements[$delta] = [
        '#theme' => 'fsa_ratings_field_score_display',
        '#title' => $this->fsaScoreTitle($items->getName()),
        '#description' => $this->fsaScoreDescription($items->getName()),
        '#score' => $this->fsaScoreTextualRepresentation($this->viewValue($item)),
      ];
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
    return nl2br(Html::escape($item->value));
  }

  /**
   * FSA Ratings score title
   *
   * @param string $fieldname
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup|string
   */
  protected function fsaScoreTitle($fieldname) {

    switch ($fieldname) {
      case 'field_score_hygiene':
        $content = $this->t('Hygienic food handling');
        break;
      case 'field_score_confidence':
        $content = $this->t('Management of food safety');
        break;
      case 'field_score_structural':
        $content = $this->t('Cleanliness and condition of facilities and building');
        break;
      default:
        $content = '';
    }
    return $content;
  }

  /**
   * FSA Ratings score description.
   *
   * @param string $fieldname
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup|string
   */
  protected function fsaScoreDescription($fieldname) {

    switch ($fieldname) {
      case 'field_score_hygiene':
        $content = $this->t('Hygienic handling of food including preparation, cooking, re-heating, cooling and storage');
        break;
      case 'field_score_confidence':
        $content = $this->t('System or checks in place to ensure that food sold or served is safe to eat, evidence that staff know about food safety, and the food safety officer has confidence that standards will be maintained in future.');
        break;
      case 'field_score_structural':
        $content = $this->t('Cleanliness and condition of facilities and building (including having appropriate layout, ventilation, hand washing facilities and pest control) to enable good food hygiene');
        break;
      default:
        $content = '';
    }
    return $content;
  }

  /**
   * Translate numeric score representation to translatable text.
   *
   * Values as in http://api.ratings.food.gov.uk/ScoreDescriptors
   *
   * @param int $score
   *   The score field value.
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup
   */
  protected function fsaScoreTextualRepresentation($score) {
    switch ($score) {
      case 0:
        $content = $this->t('Very good');
        break;
      case 5:
        $content = $this->t('Good');
        break;
      case 10:
        $content = $this->t('Generally satisfactory');
        break;
      case 20:
        $content = $this->t('Major improvement necessary');
        break;
      case 30:
        $content = $this->t('Urgent improvement necessary');
        break;
      default:
        $content = $score;
    }

    return $content;
  }

}
