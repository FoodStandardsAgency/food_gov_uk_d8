<?php

namespace Drupal\fsa_es\Plugin\views\filter;

use Drupal\taxonomy\Plugin\views\filter\TaxonomyIndexTid;

/**
 * @ViewsFilter("fsa_guidance_content_type")
 */
class GuidanceContentType extends TaxonomyIndexTid {

  /**
   * {@inheritdoc}
   *
   * No need to query.
   */
  public function query() {
  }

  /**
   * {@inheritdoc}
   *
   * Do not calculate content dependencies.
   */
  public function calculateDependencies() {
    $dependencies = parent::calculateDependencies();
    unset($dependencies['content']);

    return $dependencies;
  }

}
