<?php

namespace Drupal\fsa_ratings\Form;

use Drupal\Core\Ajax\ReplaceCommand;
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
   *    The unique ID
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

    // Return false if there is less results than one page.
    $params = RatingsSearch::getSearchParameters();
    $results = $this->searchService->search($params['language'], $params['keywords'], $params['filters'], 0, 20);
    $total_matches = $results['total'];

    // If we had less results than default do not bother building the form.
    if ($total_matches <= 20) {
      return FALSE;
    }

    $form['actions']['#type'] = 'actions';

    // Save total matches to
    $form['total_matches'] = array(
      '#type' => 'textfield', // @todo: change to hidden once working
      '#attributes' => array('class' => 'total-matches'),
      // The default amount items per page.
      '#default_value' => $total_matches,
    );

    $form['page_number'] = array(
      '#type' => 'textfield', // @todo: change to hidden once working
      '#attributes' => array('class' => 'page-number'),
      // The default amount items per page.
      '#default_value' => 10,
    );

    // Create the loader button with callback.
    $form['load_more'] = array(
      '#type' => 'submit',
      '#value' => t('Show more results'),
      '#ajax' => array(
        'callback' => array($this, 'ajaxSubmitForm'),
        'event' => 'click',
        'effect' => 'slide',
        'speed' => 500,
        'progress' => array(
          'type' => 'throbber',
        ),
      ),
    );

    return $form;
  }

  /**
   * Form submit function.
   *
   * @param array $form
   *    The form fields.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *    Form state values.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Build the non-js version later if really required.
  }

  /**
   * Ajax submit functionality.
   *
   * @param array $form
   *    Form fields.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *    Form state values.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *    The ajax response.
   */
  public function ajaxSubmitForm(array &$form, FormStateInterface $form_state) {

    $load_amount = 10;

    $data = $form_state->getValues();
////    $search_query = $data['search_query'];
    $page = $data['page_number'];
    $size = $page + $load_amount;
    $total_matches = $data['total_matches'];


    if ($total_matches < $size) {
      $last = TRUE;
      $size = $total_matches;
    }
    else {
      $last = FALSE;
    }

    $params = RatingsSearch::getSearchParameters();
    $results = $this->searchService->search($params['language'], $params['keywords'], $params['filters'], $load_amount, 20);

    $hits_shown = $data['page_number'] + 20; // @todo: increment this.

    $results = RatingsSearch::ratingSearchResults($results);
    $response = new AjaxResponse();
    $response->addCommand(new AppendCommand(
      '#search-load-more-wrapper',
      $results
    ));

    // Increase hits shown,
    $response->addCommand(new HtmlCommand(
      '.result-counter .hits-shown',
      $hits_shown
    ));

    return $response;
  }

}
