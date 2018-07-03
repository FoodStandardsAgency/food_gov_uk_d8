<?php

namespace Drupal\fsa_es\Plugin\views\filter;

use Drupal\views\Plugin\views\filter\InOperator;

/**
 * Class FilterInOperatorBase.
 */
abstract class FilterInOperatorBase extends InOperator {

  /**
   * {@inheritdoc}
   */
  public function exposedTranslate(&$form, $type) {
    parent::exposedTranslate($form, $type);

    if (isset($form['#options']['All'])) {
      $form['#options']['All'] = $this->t('All');
    }

  }

}
