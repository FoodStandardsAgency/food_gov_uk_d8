<?php

namespace Drupal\fsa_ratings\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * FSA Ratings search form in a block
 *
 * @Block(
 *   id = "fsa_ratings_search_block",
 *   admin_label = @Translation("FSA Ratings search block"),
 * )
 */
class FsaRatingsSearchBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    return array(
      '#title' => $this->t('Food hygiene ratings search'),
      '#theme' => 'fsa_ratings_search_page',
      '#form' => \Drupal::formBuilder()->getForm('Drupal\fsa_ratings\Form\FsaRatingsSearchForm'),
      '#form_header' => ['title' => 'Food hygiene ratings search'],
    );
  }

}
