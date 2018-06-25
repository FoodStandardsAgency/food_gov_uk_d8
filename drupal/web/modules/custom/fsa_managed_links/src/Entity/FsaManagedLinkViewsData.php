<?php

namespace Drupal\fsa_managed_links\Entity;

use Drupal\views\EntityViewsData;

/**
 * Provides Views data for FSA managed link entities.
 */
class FsaManagedLinkViewsData extends EntityViewsData {

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
