<?php

namespace Drupal\fsa_contactus\Element;

use Drupal\Component\Utility\Html;
use Drupal\webform\Element\WebformCompositeBase;

/**
 * Provides a 'gds_date'.
 *
 * Webform composites contain a group of sub-elements.
 *
 * @FormElement("gds_date")
 *
 * @see \Drupal\webform\Element\WebformCompositeBase
 * @see \Drupal\webform_example_composite\Element\WebformExampleComposite
 */
class GdsDate extends WebformCompositeBase {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    return parent::getInfo() + ['#theme' => 'webform_example_composite'];
  }

  /**
   * {@inheritdoc}
   */
  public static function getCompositeElements() {
    // Generate an unique ID that can be used by #states.
    $html_id = Html::getUniqueId('gds_date');

    $elements = [];
    $elements['day'] = [
      '#type' => 'number',
      '#title' => t('Day'),
      '#attributes' => [
        'data-webform-composite-id' => $html_id . '--day',
        'min' => 0,
        'max' => 31,
        'pattern' => '[0-9]*',
      ],
    ];
    $elements['month'] = [
      '#type' => 'number',
      '#title' => t('Month'),
      '#attributes' => [
        'data-webform-composite-id' => $html_id . '--month',
        'min' => 0,
        'max' => 12,
        'pattern' => '[0-9]*',
      ],

    ];
    $elements['year'] = [
      '#type' => 'number',
      '#title' => t('Year'),
      '#attributes' => [
        'data-webform-composite-id' => $html_id . '--year',
        'min' => 2010,
        'max' => 2050,
        'pattern' => '[0-9]*',
      ],
    ];
    return $elements;
  }

}
