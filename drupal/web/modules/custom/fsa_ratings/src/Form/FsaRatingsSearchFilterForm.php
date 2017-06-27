<?php

namespace Drupal\fsa_ratings\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for FSA Search filter form
 *
 * @ingroup fsa_ratings
 */
class FsaRatingsSearchFilterForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'fsa_ratings_search_filter';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {


    $form['sort'] = [
      '#type' => 'select',
      '#title' => $this->t('Sort results by'),
      '#options' => [
        'relevance' => $this->t('Relevance'),
        'ratings_desc' => $this->t('Ratings (highest to lowest)'),
        'ratings_asc' => $this->t('Rating (lowest to highest)'),
        'name_desc' => $this->t('Name (A to Z)'),
        'name_asc' => $this->t('Name (Z to A)'),
      ],
      '#default_value' => \Drupal::request()->query->get('sort'),

      // Autosubmit the filter.
      '#attributes' => [
        'onChange' => 'document.getElementById("fsa-ratings-search-filter").submit();',
      ],
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Filter'),
      '#button_type' => 'primary',
      '#attributes' => [
        'class' => ['invisible'],
      ]
    ];

    return $form;

  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // If we ever want to validate anything.
  }


  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    // Get current search query
    $query = \Drupal::request()->query->all();

    // Add sorting.
    if (!empty($form_state->getValue('sort'))) {
      $query['sort'] = $form_state->getValue('sort');
    }

    // And reload the search page with sorting params.
    $form_state->setRedirect('fsa_ratings.ratings_search', [], ['query' => $query]);

  }

}
