<?php

namespace Drupal\fsa_ratings\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\fsa_ratings\Controller\RatingsHelper;

/**
 * Form controller for FSA Search sort form.
 *
 * @ingroup fsa_ratings
 */
class FsaRatingsSearchSortForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'fsa_ratings_search_sort';
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
        'name_asc' => $this->t('Name (A to Z)'),
        'name_desc' => $this->t('Name (Z to A)'),
      ],
      '#default_value' => \Drupal::request()->query->get('sort'),

      // Autosubmit the form.
      '#attributes' => [
        'onChange' => 'document.getElementById("fsa-ratings-search-sort").submit();',
      ],
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Sort'),
      '#button_type' => 'primary',
      '#attributes' => [
        'class' => ['invisible'],
      ],
    ];

    $form['#cache'] = RatingsHelper::formCacheControl();

    return $form;

  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    // Get current search query.
    $query = \Drupal::request()->query->all();

    // Add sorting.
    if (!empty($form_state->getValue('sort'))) {
      $query['sort'] = $form_state->getValue('sort');
    }

    // And reload the search page with sorting params.
    $form_state->setRedirect('fsa_ratings.ratings_search', [], ['query' => $query]);

  }

}
