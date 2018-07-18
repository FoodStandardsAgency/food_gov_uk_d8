<?php

namespace Drupal\fsa_translation_notify\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class FsaTranslationNotifySettings.
 */
class FsaTranslationNotifySettings extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'fsa_translation_notify.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'fsa_translation_notify_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('fsa_translation_notify.settings');

    $form['info'] = [
      '#markup' => '<p>' . $this->t('Content translation notifications are 
        sent to the email address below. A notification is always sent for any 
        new content pages created, or when the \'Notify language team of this change\'
        checkbox is checked when saving new changes.') . '</p>',
    ];
    $form['email'] = [
      '#type' => 'email',
      '#title' => $this->t('Language team email address'),
      '#default_value' => $config->get('email'),
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $email = $form_state->getValue('email');
    if ($email != '' && !\Drupal::service('email.validator')->isValid($email)) {
      $form_state->setErrorByName('email', $this->t('Email value is not valid.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $this->config('fsa_translation_notify.settings')
      ->set('email', $form_state->getValue('email'))
      ->save();
  }

}
