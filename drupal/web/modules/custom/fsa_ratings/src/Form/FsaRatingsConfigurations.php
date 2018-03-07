<?php

namespace Drupal\fsa_ratings\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\fsa_ratings_import\Controller\FhrsApiController;

/**
 * FSA Ratings feature configurations.
 */
class FsaRatingsConfigurations extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'fsa_ratings_admin_form';
  }

  /**
   * Gets the configuration names that will be editable.
   *
   * @return array
   *   An array of configuration object names that are editable if called in
   *   conjunction with the trait's config() method.
   */
  protected function getEditableConfigNames() {
    return ['config.fsa_ratings'];
  }

  /**
   * Form constructor.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array
   *   The form structure.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $fsa_ratings = $this->config('config.fsa_ratings');

    if (\Drupal::state()->get('fsa_rating_import.full_import')) {
      $update_mode = $this->t('Polling for everything form the API');
    }
    else {
      $since = \Drupal::state()->get('fsa_rating_import.updated_since');
      if (!isset($since) || !is_int(strtotime($since))) {
        $since = FhrsApiController::FSA_RATING_UPDATE_SINCE;
      }
      $update_mode = $this->t('Polling for FHRS Establishment updates since "<code>@since</code>"', ['@since' => $since]);
    }

    $form['fsa_ratings_settings'] = [
      '#type' => 'details',
      '#open' => TRUE,
      '#title' => $this->t('API update mode information'),
    ];
    $form['fsa_ratings_settings']['update_mode'] = [
      '#markup' => '<p>' . $update_mode . '</p>',
    ];
    $form['fsa_ratings_settings']['api_url'] = [
      '#markup' => '<p>' . $this->t('API base URL: <a href="@url">@url</a>', ['@url' => FhrsApiController::FSA_FHRS_API_URL]) . '</p>',
    ];

    $form['fsa_ratings_hero'] = [
      '#type' => 'details',
      '#open' => TRUE,
      '#title' => $this->t('Hero copy text'),
      '#description' => $this->t('Hero copy text shown below "Food hygiene ratings" title on <a href="/hygiene-ratings">Ratings</a> related pages.'),
    ];
    $form['fsa_ratings_hero']['hero_copy'] = [
      '#type' => 'textfield',
      '#maxlength' => 255,
      '#size' => 128,
      '#title' => $this->t('Hero copy'),
      '#default_value' => $fsa_ratings->get('hero_copy') ? $fsa_ratings->get('hero_copy') : '',
    ];

    $form['fsa_ratings'] = [
      '#type' => 'details',
      '#title' => $this->t('Rating search landing page content'),
      '#description' => $this->t('The content to display on the <a href="/hygiene-ratings">Ratings search</a> landing page'),
    ];
    $form['fsa_ratings']['ratings_info_content'] = [
      '#type' => 'text_format',
      '#format' => 'full_html',
      '#title' => $this->t('Intro content'),
      '#maxlength' => NULL,
      '#default_value' => $fsa_ratings->get('ratings_info_content') ? $fsa_ratings->get('ratings_info_content') : '',
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * Form submission handler.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('config.fsa_ratings')
      ->set('hero_copy', $form_state->getValue('hero_copy'))
      ->set('ratings_info_content', $form_state->getValue('ratings_info_content')['value'])
      ->save();
    parent::submitForm($form, $form_state);
  }

}
