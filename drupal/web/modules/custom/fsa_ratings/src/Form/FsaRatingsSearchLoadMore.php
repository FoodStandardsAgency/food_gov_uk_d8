<?php

namespace Drupal\fsa_ratings\Form;

use Drupal\Core\Ajax\RemoveCommand;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\AppendCommand;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\fsa_es\SearchService;
use Drupal\fsa_ratings\Controller\RatingsSearch;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class AjaxLoadMore.
 *
 * @package Drupal\fsa_ratings\Form
 */
class FsaRatingsSearchLoadMore extends FormBase {

  /**
   * Form id getter.
   *
   * @return string
   *   The unique ID
   */
  public function getFormId() {
    return 'fsa_ratings_ajax_load_more';
  }

  /**
   * {@inheritdoc}
   *
   * @var \Drupal\fsa_es\SearchService*/
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
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $init_result_count = RatingsSearch::INITIAL_RESULTS_COUNT;

    $params = RatingsSearch::getSearchParameters();
    $results = $this->searchService->search($params['language'], $params['keywords'], $params['filters'], 0, $init_result_count);
    $total_matches = $results['total'];

    // If no search or less results than default do not build the form.
    if (($params['keywords'] == '' && empty($params['filters'])) || $total_matches <= $init_result_count) {
      return FALSE;
    }

    $form['actions']['#type'] = 'actions';

    // We need this on load more.
    $form['total_matches'] = [
      '#type' => 'hidden',
      '#attributes' => ['class' => ['total-matches']],
      '#default_value' => $total_matches,
    ];

    // Set page number for AJAX load more.
    $form['page_number'] = [
      '#type' => 'hidden',
      '#attributes' => ['class' => ['page-number']],
      '#default_value' => 1,
    ];

    // Create loader button with callback.
    $form['load_more'] = [
      '#type' => 'submit',
      '#value' => t('Show more results'),
      '#ajax' => [
        'callback' => [$this, 'ajaxSubmitForm'],
        'event' => 'click',
        'effect' => 'slide',
        'speed' => 500,
        'progress' => [
          'type' => 'throbber',
        ],
      ],
    ];

    return $form;
  }

  /**
   * Form submit function.
   *
   * @param array $form
   *   The form fields.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form state values.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Build the non-js version later if really required.
  }

  /**
   * Ajax submit functionality.
   *
   * @param array $form
   *   Form fields.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form state values.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   The ajax response.
   */
  public function ajaxSubmitForm(array &$form, FormStateInterface $form_state) {

    $items_to_add = RatingsSearch::ADDITIONAL_LOAD_COUNT;

    $data = $form_state->getValues();
    $page = $data['page_number'];
    $size = $page + $items_to_add;
    $total_matches = $data['total_matches'];

    // The offset, varies on first page and with loaded content.
    $offset = ($page == 1) ? RatingsSearch::INITIAL_RESULTS_COUNT : RatingsSearch::ADDITIONAL_LOAD_COUNT + ($page * $items_to_add);

    if ($page == 1) {
      $hits_shown = RatingsSearch::INITIAL_RESULTS_COUNT + $items_to_add;
    }
    else {
      $hits_shown = RatingsSearch::INITIAL_RESULTS_COUNT + ($page * $items_to_add);
    }

    // Check when we have loaded everything.
    if ($size >= $total_matches) {
      $last = TRUE;
    }
    else {
      $last = FALSE;
    }

    $params = RatingsSearch::getSearchParameters();
    $results = $this->searchService->search(
      $params['language'],
      $params['keywords'],
      $params['filters'],
      $items_to_add,
      $offset);

    $results = RatingsSearch::ratingSearchResults($results);
    $result_count = count($results);

    // How many items are loaded to page.
    $hits_shown = RatingsSearch::INITIAL_RESULTS_COUNT + ($page * $items_to_add);

    $response = new AjaxResponse();
    $response->addCommand(new AppendCommand(
      '#ratings-search-load-more', $results
    ));

    // Update page-number for the next callback.
    $response->addCommand(new InvokeCommand(
      '#fsa-ratings-ajax-load-more [name="page_number"]',
      'val',
      [$data['page_number'] + 1]
    ));

    // Increase result counter,.
    $response->addCommand(new HtmlCommand(
      '.result-counter .hits-shown',
      $hits_shown
    ));

    if ($last) {
      // Once everything is loaded remove the load-button.
      $response->addCommand(new RemoveCommand(
        '#fsa-ratings-ajax-load-more #edit-load-more'
      ));
    }

    return $response;
  }

}
