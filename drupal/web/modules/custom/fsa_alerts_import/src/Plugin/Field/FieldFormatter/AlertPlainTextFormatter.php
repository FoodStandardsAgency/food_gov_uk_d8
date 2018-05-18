<?php

namespace Drupal\fsa_alerts_import\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\Plugin\Field\FieldFormatter\BasicStringFormatter;

/**
 * Plugin implementation of the 'fsa_alert_formatter' formatter.
 *
 * @FieldFormatter(
 *   id = "fsa_allergen_formatter",
 *   label = @Translation("FSA Risk statement formatter"),
 *   field_types = {
 *     "string_long",
 *   }
 * )
 */
class AlertPlainTextFormatter extends BasicStringFormatter {

  /**
   * {@inheritdoc}
   */
  public function settingsSummary()
  {
    $summary = [];
    $summary[] = $this->t('Wraps HTML around the content of this field lines and adds "Allergen(s)" text.');
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    foreach ($items as $delta => $item) {
      $formatted = [];
      $lines = explode(PHP_EOL, $item->value);
      $i = 0;
        foreach ($lines as $line) {
        // Remove unnecessary line-breaks and don't care about the empty lines.
        $line = preg_replace("/\r|\n/", '', $line);
        if (strlen($line) < 1) {
          continue;
        }
        // If there is more then 1 line, first line should contain extra data.
        if (count($lines) > 1 && $i == 0) {
          $formatted[] = '<p><b>'.$this->t('Allergen(s)').': </b>' . $line. '</p>';
          $i++;
        }
        else {
          // And rest should be paragraphs.
          $formatted[] = '<p>' . $line . '</p>';
        }

        $value = implode('', $formatted);
        $elements[$delta] = [
          '#type' => 'inline_template',
          '#template' => '{{ value|nl2br }}',
          '#context' => ['value' => check_markup($value, 'full_html')],
        ];
      }

      return $elements;
    }
  }
}
