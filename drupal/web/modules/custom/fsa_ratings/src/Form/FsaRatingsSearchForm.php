<?php

namespace Drupal\fsa_ratings\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\fsa_ratings\Controller\RatingsHelper;

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
    $available_filters = $search_service->categories();

    // Remove entries with empty key
    foreach ($available_filters as $key => $value) {
      if (!$available_filters[$key][0]['key']) {
        array_shift($available_filters[$key]);
      }
    }

    // User provided search input.
    $keywords = \Drupal::request()->query->get('q');

    // User provided max item count. Hard-limit is 1000. Default is 20.
    $max_items = \Drupal::request()->query->get('max');
    if (empty($max_items) || $max_items > 1000) {
      $max_items = 20;
    }

    // See if the following parameters are provided by the user and add to the
    // list of filters.
    foreach (self::FILTER_PARAM_NAMES as $opt) {
      $value = \Drupal::request()->query->get($opt);
      if (!empty($value)) {
        $filters[$opt] = $value;
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
      if (\Drupal::request()->query->get($input)) {
        $search = TRUE;
      }
    }

    // Set default title.
    $form['header']['title'] = ['#markup' => $this->t('Food hygiene ratings search')];

    // And if search was not performed pass additional header for the form.
    if (!$search && \Drupal::routeMatch()->getRouteName() == 'fsa_ratings.ratings_search') {
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

    $form['advanced_button'] = [
      '#type' => 'item',
      '#prefix' => '<div class="toggle-button js-toggle-button ratings__advanced-search-button" role="button"  aria-expanded="false" aria-controls="collapsible-12345zxcv"><div class="toggle-button__item">More search options</div>',
      '#suffix' => '<div class="toggle-button__item toggle-button__item--icon ratings__advanced-search-button-icon"><div class="toggle-button__fallback-icon"></div></div></div>',
    ];

    $form['advanced'] = [
      '#type' => 'item',
      '#prefix' => '<div class="toggle-content js-toggle-content" id="collapsible-12345zxcv">',
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
      '#options' => $this->aggsToOptions($available_filters['business_types']),
      '#default_value' => \Drupal::request()->query->get('business_type'),
      '#empty_value' => '',
    ];
    $form['advanced']['local_authority'] = [
      '#type' => 'select',
      '#title' => $this->t('Local authority'),
      '#options' => $this->aggsToOptions($available_filters['local_authorities']),
      '#default_value' => \Drupal::request()->query->get('local_authority'),
      '#empty_value' => '',
    ];
    $form['advanced']['rating_value'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Hygiene rating'),
      '#options' => $this->aggsToOptions($available_filters['rating_values']),
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
    // Checkboxes needs to handled differently.
    if (!empty($form_state->getValue('rating_value'))) {
      $values = $form_state->getValue('rating_value');
      $selected = [];
      foreach ($values as $name => $is_selected) {
        if ((bool) $is_selected) {
          $selected[] = $name;
        }
      }

      // If rating 0 set, make sure it gets selected.
      if (is_string($values[0])) {
        $selected[] = 0;
      }

      if (!empty($selected)) {
        $query['rating_value'] = implode(',', $selected);
      }
    }

    $form_state->setRedirect('fsa_ratings.ratings_search', [], ['query' => $query]);
  }

  /**
   * Translate aggs to options.
   */
  private function aggsToOptions($aggs_bucket = []) {
    $options = [];
    foreach ($aggs_bucket as $a) {
      $options[$a['key']] = $a['key'];
    }
    return $options;
  }

}
