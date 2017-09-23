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

    $def_result_size = RatingsSearch::DEF_RESULT_SIZE;
    $def_result_loadmore = RatingsSearch::DEF_RESULT_LOADMORE;

    // Return false if there is less results than one page.
    $params = RatingsSearch::getSearchParameters();
    $results = $this->searchService->search($params['language'], $params['keywords'], $params['filters'], 0, $def_result_size);
    $total_matches = $results['total'];

    // If no search or less results than default do not build the form.
    if (($params['keywords'] == '' && empty($params['filters'])) || $total_matches <= $def_result_size) {
      return FALSE;
    }

    $form['actions']['#type'] = 'actions';

    // Save total matches to
    $form['total_matches'] = array(
      '#title' => 'total_matches',
      '#type' => 'textfield', // @todo: change to hidden once working
      '#attributes' => array('class' => 'total-matches'),
      // The default amount items per page.
      '#default_value' => $total_matches,
    );

    $form['page_number'] = array(
      '#title' => 'page_number',
      '#type' => 'textfield', // @todo: change to hidden once working
      '#attributes' => array('class' => 'page-number'),
      // The default amount items per page.
      '#default_value' => 1,
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

    $def_result_loadmore = RatingsSearch::DEF_RESULT_LOADMORE;

    $data = $form_state->getValues();
    $page = $data['page_number'];
    $size = $page + $def_result_loadmore;
    $total_matches = $data['total_matches'];

    if ($total_matches < $size) {
      $last = TRUE;
      $size = $total_matches;
    }
    else {
      $last = FALSE;
    }

    $params = RatingsSearch::getSearchParameters();
    $results = $this->searchService->search(
      $params['language'],
      $params['keywords'],
      $params['filters'],
      $def_result_loadmore,
      RatingsSearch::DEF_RESULT_SIZE);

    if ($page == 1) {
      $hits_shown = RatingsSearch::DEF_RESULT_SIZE + $def_result_loadmore;
    }
    else {
      $hits_shown = RatingsSearch::DEF_RESULT_SIZE + ($page * $def_result_loadmore);
    }

    $results = RatingsSearch::ratingSearchResults($results);
    $response = new AjaxResponse();
    $response->addCommand(new AppendCommand(
      '#search-load-more-wrapper',
      $results
    ));

    // Update form page-number field for the next callback.
    $response->addCommand(new InvokeCommand(
      '#fsa-ratings-ajax-load-more #edit-page-number',
      'val',
      array($data['page_number'] + 1)
    ));

    // Increase result counter,
    $response->addCommand(new HtmlCommand(
      '.result-counter .hits-shown',
      $hits_shown
    ));

    // Once we have loaded everything reset the info count to total.
    if ($last) {
      $response->addCommand(new ReplaceCommand(
        '#fsa-ratings-ajax-load-more #edit-load-more',
        '<p class="small">' . t('Showing all @total@ results', ['@total@' => $total_matches]) . '</p>'
      ));
    }

    return $response;
  }

}
