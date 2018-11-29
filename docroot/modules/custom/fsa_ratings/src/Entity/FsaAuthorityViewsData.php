<?php

namespace Drupal\fsa_ratings\Entity;

use Drupal\views\EntityViewsData;

/**
 * Provides Views data for FSA Authority entities.
 */
class FsaAuthorityViewsData extends EntityViewsData {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    // Additional information for Views integration, such as table joins, can be
    // put here.
    return $data;
  }

}
