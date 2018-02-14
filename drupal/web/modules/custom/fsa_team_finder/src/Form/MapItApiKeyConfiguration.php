<?php

namespace Drupal\fsa_team_finder\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class MapItApiKeyConfiguration.
 */
class MapItApiKeyConfiguration extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'fsa_map_it_api_key_configuration';
  }

  /**
   * Gets the configuration names that will be editable.
   *
   * @return array
   *   An array of configuration object names that are editable if called in
   *   conjunction with the trait's config() method.
   */
  protected function getEditableConfigNames() {
    return ['mapit_api_key'];
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
    $mapit_api_key = $this->config('config.mapit_api_key');

    // MapIt API key.
    $form['mapit_api_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('MapIt API key'),
      '#default_value' => $mapit_api_key->get('mapit_api_key') ? $mapit_api_key->get('mapit_api_key') : '',
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {}

  /**
   * Form submission handler.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    \Drupal::configFactory()
      ->getEditable('config.mapit_api_key')
      ->set('mapit_api_key', $form_state->getValue('mapit_api_key'))
      ->save();
    parent::submitForm($form, $form_state);
  }

}
