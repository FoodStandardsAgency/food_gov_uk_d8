<?php

namespace Drupal\fsa_alerts_import\Form;

use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\ConfigFormBase;

/**
 * FSA Alerts configurations.
 */
class FsaAlertsConfigurations extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'fsa_alerts_import_admin_form';
  }

  /**
   * Gets the configuration names that will be editable.
   *
   * @return array
   *   An array of configuration object names that are editable if called in
   *   conjunction with the trait's config() method.
   */
  protected function getEditableConfigNames() {
    return ['config.fsa_alerts_import'];
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
    $fsa_alerts_import = $this->config('config.fsa_alerts_import');

    // FSA Alerts API URL
    $form['fsa_alerts_import']['api_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('FSA Alerts API base URL'),
      '#default_value' => $fsa_alerts_import->get('api_url') ? $fsa_alerts_import->get('api_url') : '',
      '#description' => $this->t('The base path of FSA Alerts API.'),
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $api_url = $form_state->getValue('api_url');

    // Validate the URL.
    if (!UrlHelper::isValid($api_url, TRUE)) {
      $form_state->setErrorByName('api_url', t('API URL @url is not valid URL.', ['@url' => $api_url]));
    }
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
    $this->config('config.fsa_alerts_import')
      ->set('api_url', $form_state->getValue('api_url'))
      ->save();
    parent::submitForm($form, $form_state);
  }

}
