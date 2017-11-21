<?php

namespace Drupal\fsa_40x\Controller;

/**
 * Renders 40x pages.
 */
class Fsa40x {

  /**
   * Renders 403 page.
   *
   * @return array
   *   Render array.
   */
  public function throw403() {
    return [
      '#theme' => 'fsa_40x_response',
      '#status_code' => 403,
      '#status_description' => t('Access denied.'),
    ];
  }

  /**
   * Renders 404 page.
   *
   * @return array
   *   Render array.
   */
  public function throw404() {
    return [
      '#theme' => 'fsa_40x_response',
      '#status_code' => 404,
      '#status_description' => t('Page not found.'),
      '#search' => \Drupal::formBuilder()
        ->getForm('Drupal\search\Form\SearchBlockForm'),
    ];
  }

}
