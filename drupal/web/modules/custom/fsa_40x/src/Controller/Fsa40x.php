<?php

namespace Drupal\fsa_40x\Controller;

class Fsa40x {
  /**
   * @return array
   */
  public function throw403() {
    return array(
      '#theme' => 'fsa_40x_response',
      '#status_code' => 403,
      '#status_description' => t('Access denied.'),
    );
  }

  /**
   * @return array
   */
  public function throw404() {
    return array(
      '#theme' => 'fsa_40x_response',
      '#status_code' => 404,
      '#status_description' => t('Page not found.'),
      '#search' => \Drupal::formBuilder()
        ->getForm('Drupal\search\Form\SearchBlockForm'),
    );
  }
}
