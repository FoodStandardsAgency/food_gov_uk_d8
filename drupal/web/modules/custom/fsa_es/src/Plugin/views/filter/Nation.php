<?php

namespace Drupal\fsa_es\Plugin\views\filter;

use Drupal\views\Plugin\views\display\DisplayPluginBase;
use Drupal\views\ViewExecutable;

/**
 * @ViewsFilter("fsa_nation")
 */
class Nation extends FilterInOperatorBase {

  /**
   * {@inheritdoc}
   */
  public function init(ViewExecutable $view, DisplayPluginBase $display, array &$options = NULL) {
    parent::init($view, $display, $options);
    /** @var \Drupal\elasticsearch_helper_views\Plugin\views\query\Elasticsearch $query */
    $query = $this->view->getQuery();
    $this->definition['options callback'] = [$query->getQueryBuilder(), 'getNationFilterOptions'];
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
