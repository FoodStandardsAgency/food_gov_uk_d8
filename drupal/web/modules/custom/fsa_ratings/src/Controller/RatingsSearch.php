<?php

namespace Drupal\fsa_ratings\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\fsa_es\SearchService;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class RatingsSearch.
 *
 * @package Drupal\fsa_ratings\Controller
 */
class RatingsSearch extends ControllerBase {

  /** @var SearchService  */
  private $searchService;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('fsa_es.search_service')
    );
  }


  /**
   * {@inheritdoc}
   */
  public function __construct(SearchService $searchService) {
    $this->searchService = $searchService;
  }


  /**
   * Page callback for /ratings/search/{keywords}
   *
   */
  public function ratingsSearch($keywords) {
    $items = [];
    $categories = [];

    if (!empty($keywords)) {
      $results = $this->searchService->search($keywords);

      foreach ($results['results'] as $result) {
        $items[] = [
          '#theme' => 'fsa_ratings_search_result_item',
          '#item' => $result,
        ];
      }

      // Collect the aggregation items of the 'types' of business.
      foreach ($results['types'] as $type) {
        $categories[] = [
          'count' => $type['doc_count'],
          'label' => $type['key'],
        ];
      }

    }

    return [
      '#theme' => 'fsa_ratings_search_page',
      '#items' => $items, // The actual result items
      '#categories' => $categories, // The aggregation results, list of categories of the result items
      '#keywords' => $keywords, // The keywords given in the URL
    ];
  }

}
