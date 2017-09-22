<?php

namespace Drupal\fsa_custom\Plugin\views\filter;

use Drupal\views\Plugin\views\filter\InOperator;
use Drupal\views\ViewExecutable;
use Drupal\views\Plugin\views\display\DisplayPluginBase;

/**
 * Filters content by year of created date.
 *
 * @ingroup views_filter_handlers
 *
 * @ViewsFilter("year_select")
 */
class YearSelect extends InOperator {

  protected $bundle;

  /**
   * {@inheritdoc}
   */
  public function init(ViewExecutable $view, DisplayPluginBase $display, array &$options = NULL) {
    parent::init($view, $display, $options);

    if (isset($display->handlers['filter']['type'])) {
      $bundle = reset($display->handlers['filter']['type']->value);
      $this->bundle = $bundle;
    }

    $this->valueTitle = t('Created year');
    $this->definition['options callback'] = array($this, 'generateOptions');
  }

  /**
   * Override the query.
   */
  public function query() {
    if (isset($this->value) && !empty($this->value[0])) {
      $this->query->addWhereExpression('AND', 'FROM_UNIXTIME(node_field_data.created, \'%Y\') = ' . $this->value[0]);
    }
  }

  /**
   * Skip validation if no options have been chosen.
   */
  public function validate() {
    if (!empty($this->value)) {
      parent::validate();
    }
  }

  /**
   * Helper function that generates the options.
   */
  public function generateOptions() {
    $years = range(date('Y'), 1970);
    $years_processed = array();

    foreach ($years as $year) {

      // Add count query to check for results.
      $query = \Drupal::entityQuery('node');
      $query->condition('created', [strtotime('01/01/' . $year), strtotime('12/31/' . $year)], 'BETWEEN');
      $query->condition('status', 1);
      if ($this->bundle) {
        $query->condition('type', $this->bundle);
      }
      $result = (int) $query->count()->execute();

      if ($result > 0) {
        $years_processed[$year] = $year;
      }
    }

    return $years_processed;
  }

}
