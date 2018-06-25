<?php

namespace Drupal\fsa_content_reminder\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class FsaContentReminderSettings.
 */
class FsaContentReminderSettings extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'fsa_content_reminder.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'fsa_content_reminder_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $next_dispatch = \Drupal::state()->get('fsa_content_reminder.next_dispatch');
    $date = \Drupal::service('date.formatter')->format($next_dispatch, 'short');
    $config = $this->config('fsa_content_reminder.settings');

    $form['next_dispatch'] = [
      '#markup' => '<p>' . $this->t('Next content reminder emails will trigger on @date', ['@date' => $date]) . '</p>',
    ];;
    $form['email'] = [
      '#type' => 'email',
      '#title' => $this->t('Email'),
      '#description' => $this->t('Email address to send the content reminders'),
      '#default_value' => $config->get('email'),
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $this->config('fsa_content_reminder.settings')
      ->set('email', $form_state->getValue('email'))
      ->save();
  }

}
