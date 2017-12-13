<?php

namespace Drupal\fsa_es\Plugin\views\filter;

use Drupal\views\Plugin\views\display\DisplayPluginBase;
use Drupal\views\Plugin\views\filter\InOperator;
use Drupal\views\ViewExecutable;

/**
 * @ViewsFilter("fsa_ratings_fhis_rating_value")
 */
class RatingsFhisRatingValue extends InOperator {

  /**
   * {@inheritdoc}
   */
  public function init(ViewExecutable $view, DisplayPluginBase $display, array &$options = NULL) {
    parent::init($view, $display, $options);
    /** @var \Drupal\elasticsearch_helper_views\Plugin\views\query\Elasticsearch $query */
    $query = $this->view->getQuery();
    $this->definition['options callback'] = [$query->getQueryBuilder(), 'getFhisRatingValueFilterOptions'];
  }

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
