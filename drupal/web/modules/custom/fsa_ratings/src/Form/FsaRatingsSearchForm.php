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
    'fhis_rating_value',
    'fhrs_rating_value',
  ];

  const FILTER_PARAM_NAMES = [
    'local_authority',
    'business_type',
    'rating_value',
    'fhis_rating_value',
    'fhrs_rating_value',
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

    // Attach JS to toggle FHRS/FHIS checkbox selecting.
    $form['#attached']['library'][] = 'fsa_ratings/ratings_search';

    $params = RatingsSearch::getSearchParameters();
    $language = \Drupal::languageManager()->getCurrentLanguage();
    $available_filters = $search_service->categories($language);

    // User provided search input.
    $keywords = $params['keywords'];

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
        $is_open = ' is-visible';
        $aria_expanded = 'true';
        break;
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
      '#attributes' => [
        'class' => [
          'js-main-search-input',
        ],
      ],
    ];

    $string = $this->t('More search options');

    $form['advanced_button'] = [
      '#type' => 'item',
      '#prefix' => '<div class="toggle-button ratings__advanced-search-button' . $is_open . '" role="button" aria-expanded="' . $aria_expanded . '" data-state="is-open" data-theme="dynamic" data-state-element="#collapsible-12345zxcv" aria-controls="collapsible-12345zxcv"><div class="toggle-button__item">' . $string . '</div>',
      '#suffix' => '<div class="toggle-button__item toggle-button__item--icon ratings__advanced-search-button-icon"><div class="toggle-button__fallback-icon"></div></div></div>',
    ];

    $form['advanced'] = [
      '#type' => 'item',
      '#prefix' => '<div class="toggle-content ratings__advanced-search-content js-toggle-content' . $is_open . '" id="collapsible-12345zxcv">',
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
      '#options' => $search_service->aggsToOptions($available_filters['business_types']),
      '#default_value' => isset($params['filters']['business_type']) ? $params['filters']['business_type'] : NULL,
      '#empty_value' => '',
    ];
    $form['advanced']['local_authority'] = [
      '#type' => 'select',
      '#title' => $this->t('Country or local authority'),
      '#empty_option' => $this->t('All'),
      '#options' => $search_service->aggsToOptions($available_filters['local_authorities']),
      '#default_value' => isset($params['filters']['local_authority']) ? $params['filters']['local_authority'] : NULL,
      '#empty_value' => '',
    ];

    $fhrs_rating_default_value = isset($params['filters']['fhrs_rating_value']) ? $params['filters']['fhrs_rating_value'] : '';
    $form['advanced']['fhrs_rating_value'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Hygiene rating') . ' <span class="regions">(' . $this->t('England, Northern Ireland, Wales') . ')</span>',
      '#options' => $search_service->defineAndSortArrayItems(
        $search_service->aggsToOptions($available_filters['fhrs_rating_values']),
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
      ),
      '#default_value' => is_array($fhrs_rating_default_value) ? $fhrs_rating_default_value : explode(',', $fhrs_rating_default_value),
    ];

    $fhis_rating_default_value = isset($params['filters']['fhis_rating_value']) ? $params['filters']['fhis_rating_value'] : '';
    $form['advanced']['fhis_rating_value'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Hygiene status') . ' <span class="regions">(' . $this->t('Scotland') . ')</span>',
      '#options' => $search_service->defineAndSortArrayItems(
        $search_service->aggsToOptions($available_filters['fhis_rating_values']),
        [
          'Pass',
          'Pass and Eat Safe',
          'Improvement Required',
          'Awaiting Inspection',
          'Exempt',
        ]
      ),
      '#default_value' => is_array($fhis_rating_default_value) ? $fhis_rating_default_value : explode(',', $fhis_rating_default_value),
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
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $query = [];

    // Read all the single values.
    foreach (['q', 'business_type', 'local_authority'] as $p) {
      if (!empty($form_state->getValue($p))) {
        $query[$p] = $form_state->getValue($p);
      }
    }

    // Read FHIS & FHRS rating values similarly.
    $rating_values = [
      'fhis',
      'fhrs',
    ];
    foreach ($rating_values as $scheme) {
      if (!empty($form_state->getValue($scheme . '_rating_value'))) {
        $selected = [];
        $values = $form_state->getValue($scheme . '_rating_value');
        foreach ($values as $name => $is_selected) {
          if ($is_selected) {
            $selected[] = $name;
          }
        }

        // If rating 0 set, make sure it gets selected.
        if (isset($values[0]) && is_string($values[0])) {
          $selected[] = 0;
        }

        if (!empty($selected)) {
          // Build query for rating_value.
          $query[$scheme . '_rating_value'] = implode(',', $selected);
        }
      }
    }

    if (empty($query)) {
      drupal_set_message(t('Please enter a business name/location or use search options below.'), 'warning');
      $fragment = FALSE;
    }
    else {
      $fragment = RatingsHelper::RESULTS_ANCHOR;
    }

    $form_state->setRedirect('fsa_ratings.ratings_search', [], ['query' => $query, 'fragment' => $fragment]);
  }

}
