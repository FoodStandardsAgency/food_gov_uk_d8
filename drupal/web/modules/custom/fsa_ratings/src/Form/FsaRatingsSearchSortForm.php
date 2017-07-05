<?php

namespace Drupal\fsa_ratings\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\fsa_ratings\Controller\RatingsHelper;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Form controller for FSA Search sort form.
 *
 * @ingroup fsa_ratings
 */
class FsaRatingsSearchSortForm extends FormBase {

  /**
   * Request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  public $request;

  /**
   * Class constructor.
   *
   * @param \Symfony\Component\HttpFoundation\RequestStack $request
   *   Request stack.
   */
  public function __construct(RequestStack $request) {
    $this->request = $request;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
    // Load the service required to construct this class.
      $container->get('request_stack')
    );
  }

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
      '#default_value' => $this->request->getCurrentRequest()->query->get('sort'),

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
    $query = $this->request->getCurrentRequest()->query->all();

    // Add sorting.
    if (!empty($form_state->getValue('sort'))) {
      $query['sort'] = $form_state->getValue('sort');
    }

    // And reload the search page with sorting params.
    $form_state->setRedirect('fsa_ratings.ratings_search', [], ['query' => $query]);

  }

}
