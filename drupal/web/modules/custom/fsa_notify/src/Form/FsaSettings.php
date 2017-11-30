<?php

namespace Drupal\fsa_notify\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * FSA notify settings page.
 */
class FsaSettings extends FormBase {

  private $state_key = 'fsa_notify.killswitch';

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'fsa_notify.settings';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $weight = 0;

    $form['note'] = [
      '#type' => 'item',
      '#plain_text' => t('Please get following values from https://www.notifications.service.gov.uk/'),
      '#weight' => $weight++,
    ];

    $keys = [
      'fsa_notify.bearer_token' => t('Notify: Bearer token'),
      'fsa_notify.api' => t('Notify API: API key'),
      'fsa_notify.template_email' => t('Notify API: Template ID: Email'),
      'fsa_notify.template_sms' => t('Notify API: Template ID: Sms'),
    ];

    foreach ($keys as $key => $title) {
      // Cannot have dot in render array key.
      $key2 = str_replace('.', '___', $key);
      $value = \Drupal::state()->get($key);
      $form[$key2] = [
        '#type' => 'textfield',
        '#title' => $title,
        '#default_value' => $value,
        '#weight' => $weight++,
      ];
    }

    $killswitch = \Drupal::state()->get($this->state_key);
    $killswitch = (bool) $killswitch;

    $form['status_old'] = [
      '#type' => 'value',
      '#value' => $killswitch,
      '#weight' => $weight++,
    ];

    $form['status_new'] = [
      '#type' => 'checkbox',
      '#title' => t('Collect notifications and send out to subscribers.'),
      '#default_value' => $killswitch,
      '#weight' => $weight++,
    ];

    $form['log_callback_errors'] = [
      '#type' => 'checkbox',
      '#title' => t('Log all Notify callback errors.'),
      '#default_value' => \Drupal::state()->get('fsa_notify.log_callback_errors'),
      '#weight' => $weight++,
    ];

    $form['actions'] = [
      '#type' => 'actions',
      '#weight' => $weight++,
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save'),
      '#button_type' => 'primary',
    ];

    $query = \Drupal::entityQuery('user');
    $query->condition('uid', 0, '>');
    $query->condition('status', 1);
    $query->count();
    $count = $query->execute();
    $form['user_status_enabled'] = [
      '#type' => 'item',
      '#title' => t('Enabled users'),
      '#plain_text' => $count,
      '#weight' => $weight++,
    ];

    $query = \Drupal::entityQuery('user');
    $query->condition('uid', 0, '>');
    $query->condition('status', 0);
    $query->count();
    $count = $query->execute();
    $form['user_status_disabled'] = [
      '#type' => 'item',
      '#title' => t('Disabled users'),
      '#plain_text' => $count,
      '#weight' => $weight++,
    ];

    $form['stats_note'] = [
      '#type' => 'item',
      '#plain_text' => t('Following stats is counted only per enabled users:'),
      '#weight' => $weight++,
    ];

    $entityManager = \Drupal::service('entity_field.manager');
    $fields = $entityManager->getFieldStorageDefinitions('user', 'user');
    $methods = options_allowed_values($fields['field_notification_method']);
    foreach ($methods as $key => $description) {
      $query = \Drupal::entityQuery('user');
      $query->condition('uid', 0, '>');
      $query->condition('status', 1);
      $query->condition('field_notification_method', $key);
      $query->count();
      $count = $query->execute();
      $form["user_method_$key"] = [
        '#type' => 'item',
        '#title' => $description,
        '#plain_text' => $count,
        '#weight' => $weight++,
      ];
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $keys = [
      'fsa_notify.bearer_token',
      'fsa_notify.api',
      'fsa_notify.template_email',
      'fsa_notify.template_sms',
    ];

    foreach ($keys as $key) {
      // Cannot have dot in render array key.
      $key2 = str_replace('.', '___', $key);
      $value = $form_state->getValue($key2);
      \Drupal::state()->set($key, $value);
    }

    $status_old = $form_state->getValue('status_old');
    $status_new = $form_state->getValue('status_new');

    if (empty($status_new)) {
      \Drupal::state()->delete($this->state_key);
    }
    else {
      \Drupal::state()->set($this->state_key, 1);
    }

    if (empty($status_old) && !empty($status_new)) {
      drupal_set_message(t('Notification system is now ENABLED.'));
    }

    if (!empty($status_old) && empty($status_new)) {
      drupal_set_message(t('Notification system is now DISABLED.'));
    }

    if (empty($form_state->getValue('log_callback_errors'))) {
      \Drupal::state()->delete('fsa_notify.log_callback_errors');
    }
    else {
      \Drupal::state()->set('fsa_notify.log_callback_errors', 1);
    }

    // Let the user know something happened.
    drupal_set_message($this->t('Notify settings updated.'));

  }

}
