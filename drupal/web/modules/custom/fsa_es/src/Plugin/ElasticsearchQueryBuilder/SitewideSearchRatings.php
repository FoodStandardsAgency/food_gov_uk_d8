<?php

namespace Drupal\fsa_es\Plugin\ElasticsearchQueryBuilder;

use Drupal\views\ViewExecutable;

/**
 * @ElasticsearchQueryBuilder(
 *   id = "sitewide_search_ratings",
 *   label = @Translation("Ratings"),
 *   description = @Translation("Provides query builder for sitewide ratings search.")
 * )
 */
class SitewideSearchRatings extends SitewideSearchBase {

  /**
   * {@inheritdoc}
   */
  public function buildQuery(ViewExecutable $view) {
    return [
      'body' => [
        'query' => [
          'match_all' => new \stdClass(),
        ],
      ],
    ];
  }

}
