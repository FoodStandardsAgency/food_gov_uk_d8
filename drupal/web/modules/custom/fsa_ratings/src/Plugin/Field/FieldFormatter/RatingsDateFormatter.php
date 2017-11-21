<?php

namespace Drupal\fsa_ratings\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\fsa_ratings\Controller\RatingsHelper;

/**
 * Plugin implementation of the 'fsa_ratingdate_formatter'.
 *
 * Formats FHRS Ratingdate.
 *
 * @FieldFormatter(
 *   id = "fsa_ratingdate_formatter",
 *   label = @Translation("FSA Ratingdate"),
 *   field_types = {
 *      "datetime"
 *   }
 * )
 */
class RatingsDateFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary[] = $this->t('Format date and display "N/A" for dates before 1970.');

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    foreach ($items as $delta => $item) {
      // Send the date to be formatted by RatingsHelper::ratingsDate().
      $elements[$delta] = [
        '#markup' => RatingsHelper::ratingsDate($item->getValue()['value']),
      ];
    }

    return $elements;
  }

}
