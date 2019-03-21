<?php

namespace Drupal\fsa_translate\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class TranslationRequiredConfiguration.
 */
class TranslationRequiredConfiguration extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'fsa_translate_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['config.fsa_translate'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('config.fsa_translate');

    // Settings container.
    $form['mail'] = [
      '#type' => 'details',
      '#title' => $this->t('Mail settings'),
      '#open' => TRUE,
    ];

    // Mail recipient.
    $form['mail']['recipient'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Recipient'),
      '#default_value' => $config->get('translation_required_mail_recipient'),
    ];

    // Mail subject.
    $form['mail']['subject'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Subject'),
      '#default_value' => $config->get('translation_required_mail_subject'),
    ];

    // Mail body.
    $form['mail']['body'] = [
      '#type' => 'text_format',
      '#title' => $this->t('Body'),
      '#format' => 'basic_html',
      '#default_value' => $config->get('translation_required_mail_body'),
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    // Set submitted configuration settings.
    $this->configFactory->getEditable('config.fsa_translate')
      ->set('translation_required_mail_recipient', $form_state->getValue('recipient'))
      ->set('translation_required_mail_subject', $form_state->getValue('subject'))
      ->set('translation_required_mail_body', $form_state->getValue('body')['value'])
      ->save();
    parent::submitForm($form, $form_state);
  }

}
