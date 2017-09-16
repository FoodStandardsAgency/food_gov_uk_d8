<?php

/**
 * @file
 * Contains \Drupal\fsa_notify\Form\FsaSettings.
 */

namespace Drupal\fsa_notify\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

class FsaSettings extends FormBase {

  private $state_key = 'fsa_notify.killswitch';

  /**
   * {@inheritdoc}
   */

  public function getFormId() {
    return 'fsa_notify.settings';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['note'] = [
      '#type' => 'item',
      '#plain_text' => t('Please get following values from https://www.notifications.service.gov.uk/'),
    ];

    $keys = [
      'fsa_notify.api' => t('Notify API: API key'),
      'fsa_notify.template_email' => t('Notify API: Template ID: Email'),
      'fsa_notify.template_sms' => t('Notify API: Template ID: Sms'),
    ];

    foreach ($keys as $key => $title) {
      // cannot have dot in render array key
      $key2 = str_replace('.', '___', $key);
      $value = \Drupal::state()->get($key);
      $form[$key2] = [
        '#type' => 'textfield',
        '#title' => $title,
        '#required' => TRUE,
        '#default_value' => $value,
      ];
    }

    $killswitch = \Drupal::state()->get($this->state_key);
    $killswitch = (bool) $killswitch;

    $form['status_old'] = [
      '#type' => 'value',
      '#value' => $killswitch,
    ];

    $form['status_new'] = [
      '#type' => 'checkbox',
      '#title' => t('Collect notifications and send out to subscribers.'),
      '#default_value' => $killswitch,
    ];

    $form['actions']['#type'] = 'actions';

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save'),
      '#button_type' => 'primary',
    ];

    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {

    $keys = [
      'fsa_notify.api',
      'fsa_notify.template_email',
      'fsa_notify.template_sms',
    ];

    foreach ($keys as $key) {
      // cannot have dot in render array key
      $key2 = str_replace('.', '___', $key);
      $value = $form_state->getValue($key2);
      \Drupal::state()->set($key, $value);
    }

    $status_old = $form_state->getValue('status_old');
    $status_new = $form_state->getValue('status_new');

    if (empty($status_new)) {
      \Drupal::state()->delete($this->state_key);
    } else {
      \Drupal::state()->set($this->state_key, 1);
    }

    if (empty($status_old) && !empty($status_new)) {
      drupal_set_message(t('Notification system is now ENABLED.'));
    }

    if (!empty($status_old) && empty($status_new)) {
      drupal_set_message(t('Notification system is now DISABLED.'));
    }

  }

}
