<?php

namespace Drupal\fsa_es\Plugin\views\filter;

/**
 * Class FilterExposedCheckboxTrait
 */
trait FilterExposedCheckboxTrait {

  /**
   * {@inheritdoc}
   */
  public function exposedTranslate(&$form, $type) {
    parent::exposedTranslate($form, $type);

    // Expose filter as checkboxes.
    $form['#type'] = 'checkboxes';

    // Filter out empty values.
    $form['#options'] = array_filter($form['#options']);
  }

}
