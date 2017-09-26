<?php

namespace Drupal\fsa_ratings\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\fsa_ratings\Controller\RatingsHelper;
use Drupal\fsa_ratings\Controller\RatingsSearch;

/**
 * Form controller for FSA Establishment edit forms.
 *
 * @ingroup fsa_ratings
 */
class FsaRatingsSearchForm extends FormBase {

  const FORM_FIELDS = [
    'q',
    'local_authority',
    'business_type',
    'rating_value',
  ];

  const FILTER_PARAM_NAMES = [
    'local_authority',
    'business_type',
    'rating_value',
  ];

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'fsa_ratings_search';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    /** @var \Drupal\fsa_es\SearchService $search_service */
    $search_service = \Drupal::service('fsa_es.search_service');

    $filters = [];
    $language = \Drupal::languageManager()->getCurrentLanguage();
    $available_filters = $search_service->categories($language);

    // User provided search input.
    $keywords = \Drupal::request()->query->get('q');

    // User provided max item count. Hard-limit is 1000. Default is 20.
    $max_items = \Drupal::request()->query->get('max');
    if (empty($max_items) || $max_items > 1000) {
      $max_items = RatingsSearch::INITIAL_RESULTS_COUNT;
    }

    // See if the following parameters are provided by the user and add to the
    // list of filters ("advanced search options"). Additionally send
    // appropriate classes to html for keeping the options open if there was a
    // value.
    $is_open = '';
    $aria_expanded = 'false';
    foreach (self::FILTER_PARAM_NAMES as $opt) {
      $value = \Drupal::request()->query->get($opt);
      if (!empty($value)) {
        $filters[$opt] = $value;
        $is_open = ' is-open';
        $aria_expanded = 'true';
      }
    }

    // Build search form header.
    $form['header'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => [
          'fsa-rating-search-header',
        ],
      ],
    ];

    // Detect from the query params if search was performed.
    $search = FALSE;
    foreach (self::FORM_FIELDS as $input) {
      $query_input = \Drupal::request()->query->get($input);
      if (isset($query_input)) {
        $search = TRUE;
      }
    }

    // Set default title.
    $form['header']['title'] = ['#markup' => $this->t('Food hygiene ratings search')];

    // ...and if search was not performed pass additional header texts to the form.
    if ($search === FALSE && \Drupal::routeMatch()->getRouteName() == 'fsa_ratings.ratings_search') {
      $form['header']['title'] = ['#markup' => $this->t('Eating out?')];
      $form['header']['subtitle'] = ['#markup' => $this->t('Check the hygiene rating.')];
      $form['header']['copy'] = ['#markup' => $this->t('Find out if a restaurant, takeaway or food shop you want to visit has good food hygiene standards.')];
    }

    $form['main'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => [
          'fsa-rating-search-main',
        ],
      ],
    ];
    $form['main']['q'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Business name/or location'),
      '#default_value' => $keywords,
    ];

    $string = $this->t('More search options');

    $form['advanced_button'] = [
      '#type' => 'item',
      '#prefix' => '<div class="toggle-button js-toggle-button ratings__advanced-search-button' . $is_open . '" role="button" aria-expanded="' . $aria_expanded . '" aria-controls="collapsible-12345zxcv"><div class="toggle-button__item">' . $string . '</div>',
      '#suffix' => '<div class="toggle-button__item toggle-button__item--icon ratings__advanced-search-button-icon"><div class="toggle-button__fallback-icon"></div></div></div>',
    ];

    $form['advanced'] = [
      '#type' => 'item',
      '#prefix' => '<div class="toggle-content js-toggle-content' . $is_open . '" id="collapsible-12345zxcv">',
      '#suffix' => '</div>',
      '#attributes' => [
        'class' => [
          'fsa-rating-search-advanced',
        ],
      ],
    ];
    $form['advanced']['business_type'] = [
      '#type' => 'select',
      '#title' => $this->t('Business type'),
      '#empty_option' => $this->t('All'),
      '#options' => $this->aggsToOptions($available_filters['business_types']),
      '#default_value' => \Drupal::request()->query->get('business_type'),
      '#empty_value' => '',
    ];
    $form['advanced']['local_authority'] = [
      '#type' => 'select',
      '#title' => $this->t('Country or local authority'),
      '#empty_option' => $this->t('All'),
      '#options' => $this->aggsToOptions($available_filters['local_authorities']),
      '#default_value' => \Drupal::request()->query->get('local_authority'),
      '#empty_value' => '',
    ];

    // Define FHRS options and their sorting.
    $rating_values = $this->defineAndSortArrayItems(
      $this->aggsToOptions($available_filters['rating_values']),
      [
        5,
        4,
        3,
        2,
        1,
        0,
        'AwaitingInspection',
        'Exempt',
      ]
    );

    $form['advanced']['rating_value_fhrs'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Hygiene rating') . ' <span class="regions">(' . $this->t('England, Northern Ireland, Wales') . ')</span>',
      '#options' => $rating_values,
      '#default_value' => explode(',', \Drupal::request()->query->get('rating_value')),
    ];

    // Define scottish ratings & their order.
    $rating_values = $this->defineAndSortArrayItems(
      $this->aggsToOptions($available_filters['rating_values']),
      [
        'Pass',
        'Pass and Eat Safe',
        'Improvement Required',
        'Awaiting Inspection',
        'Exempt',
      ]
    );
    $form['advanced']['rating_value_fhis'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Hygiene status') . ' <span class="regions">(' . $this->t('Scotland') . ')</span>',
      '#options' => $rating_values,
      '#default_value' => explode(',', \Drupal::request()->query->get('rating_value')),
    ];

    $form['container']['actions']['#type'] = 'actions';
    $form['container']['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Search'),
      '#button_type' => 'primary',
    ];

    $form['#theme'] = 'fsa_ratings_search_form';

    $form['#cache'] = RatingsHelper::formCacheControl();

    return $form;

  }

  /**
   * Private helper function to sort array by another array.
   *
   * @param array $array
   *   The array to define..
   * @param array $definingArray
   *   The array with keys defining sort and items to keep.
   *
   * @return array
   *   Sorted array.
   */
  private static function defineAndSortArrayItems(array $array, array $definingArray) {
    $modified_array = array();
    foreach ($definingArray as $key) {
      if (array_key_exists($key, $array)) {
        $modified_array[$key] = $array[$key];
        unset($array[$key]);
      }
    }
    return $modified_array;
  }


  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $query = [];
    $rating_fhrs = FALSE;
    $rating_fhis = FALSE;

    // Read all the single values.
    foreach (['q', 'business_type', 'local_authority'] as $p) {
      if (!empty($form_state->getValue($p))) {
        $query[$p] = $form_state->getValue($p);
      }
    }

    // Read FHRS rating_value(s) from checkboxes.
    if (!empty($form_state->getValue('rating_value_fhrs'))) {
      $selected = [];
      $values = $form_state->getValue('rating_value_fhrs');
      foreach ($values as $name => $is_selected) {
        if ($is_selected) {
          $selected[] = $name;
        }
      }

      // If rating 0 set, make sure it gets selected.
      if (is_string($values[0])) {
        $selected[] = 0;
      }

      if (!empty($selected)) {
        // Build query for rating_value.
        $rating_fhrs = implode(',', $selected);
      }
    }

    // Read FHIS rating_value(s) from checkboxes.
    if (!empty($form_state->getValue('rating_value_fhis'))) {
      $selected = [];
      $values = $form_state->getValue('rating_value_fhis');
      foreach ($values as $name => $is_selected) {
        if ($is_selected) {
          $selected[] = $name;
        }
      }

      if (!empty($selected)) {
        // Build query for rating_value.
        $rating_fhis = implode(',', $selected);
      }
    }

    // Merge FHRS & FHIS checkbox values to the query string.
    if ($rating_fhrs || $rating_fhis) {
      $query['rating_value'] = $rating_fhrs . ',' . $rating_fhis;
    }

    $form_state->setRedirect('fsa_ratings.ratings_search', [], ['query' => $query]);
  }

  /**
   * Translate aggs to options.
   */
  private function aggsToOptions($aggs_bucket = []) {
    $options = [];
    foreach ($aggs_bucket as $a) {
      // Add textual representation for numeric values.
      switch ($a['key']) {
        case '5':
          $value = $a['key'] .' ' . t('Very good');
          break;
        case '4':
          $value = $a['key'] .' ' . t('Good');
          break;
        case '3':
          $value = $a['key'] .' ' . t('Generally satisfactory');
          break;
        case '2':
          $value = $a['key'] .' ' . t('Improvement necessary');
          break;
        case '1':
          $value = $a['key'] .' ' . t('Major improvement necessary');
          break;
        case '0':
          $value = $a['key'] .' ' . t('Urgent improvement necessary');
          break;
        case 'AwaitingInspection':
          // Make this label more human friendly.
          $value = t('Awaiting Rating');
          break;
        default:
          $value = $a['key'];
      }
      $options[$a['key']] = (string) $value;
    }
    return $options;
  }

}
