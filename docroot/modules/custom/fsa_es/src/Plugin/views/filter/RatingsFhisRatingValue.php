<?php

namespace Drupal\fsa_es\Plugin\views\filter;

use Drupal\views\Plugin\views\display\DisplayPluginBase;
use Drupal\views\ViewExecutable;

/**
 * @ViewsFilter("fsa_ratings_fhis_rating_value")
 */
class RatingsFhisRatingValue extends FilterInOperatorBase {

  use FilterExposedCheckboxTrait;

  /**
   * {@inheritdoc}
   */
  public function init(ViewExecutable $view, DisplayPluginBase $display, array &$options = NULL) {
    parent::init($view, $display, $options);
    /** @var \Drupal\elasticsearch_helper_views\Plugin\views\query\Elasticsearch $query */
    $query = $this->view->getQuery();
    $this->definition['options callback'] = [$query->getQueryBuilder(), 'getFhisRatingValueFilterOptions'];
  }

}
