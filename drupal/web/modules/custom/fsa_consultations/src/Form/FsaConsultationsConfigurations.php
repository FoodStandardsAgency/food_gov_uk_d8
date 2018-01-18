<?php

namespace Drupal\fsa_consultations\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\ConfigFormBase;

/**
 * FSA Consultations configurations.
 */
class FsaConsultationsConfigurations extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'fsa_consultations_admin_form';
  }

  /**
   * Gets the configuration names that will be editable.
   *
   * @return array
   *   An array of configuration object names that are editable if called in
   *   conjunction with the trait's config() method.
   */
  protected function getEditableConfigNames() {
    return ['config.fsa_consultations'];
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
    $fsa_consultations = $this->config('config.fsa_consultations');

    $form['fsa_consultations'] = [
      '#type' => 'details',
      '#open' => TRUE,
      '#title' => $this->t('Standard text'),
      '#description' => $this->t('The content to be displayed at the bottom of every <strong>Consultation</strong>.'),
    ];
    $form['fsa_consultations']['consultation_standard_text'] = [
      '#type' => 'text_format',
      '#format' => 'full_html',
      '#title' => $this->t('Standard text'),
      '#maxlength' => NULL,
      '#default_value' => $fsa_consultations->get('consultation_standard_text') ? $fsa_consultations->get('consultation_standard_text') : '',
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
    $this->config('config.fsa_consultations')
      ->set('consultation_standard_text', $form_state->getValue('consultation_standard_text')['value'])
      ->save();
    parent::submitForm($form, $form_state);
  }

}
