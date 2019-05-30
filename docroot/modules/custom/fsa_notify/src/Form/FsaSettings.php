<?php

namespace Drupal\fsa_notify\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * FSA notify settings page.
 */
class FsaSettings extends FormBase {

  private $stateKey = 'fsa_notify.killswitch';

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
    $date_formatter = \Drupal::service('date.formatter');

    $form['note'] = [
      '#type' => 'item',
      '#markup' => t('Get Notify API keys and tokens from <a href="https://www.notifications.service.gov.uk/services/6f00837a-4b8f-4ddd-ae96-ca2d3035fe57">www.notifications.service.gov.uk</a>'),
      '#weight' => $weight++,
    ];

    $keys = [
      'fsa_notify.bearer_token' => t('Notify Bearer token'),
      'fsa_notify.api' => t('Notify API key'),
      'fsa_notify.template_email' => t('Notify Email template ID (English)'),
      'fsa_notify.template_email_cy' => t('Notify Email template ID (Welsh)'),
      'fsa_notify.template_sms' => t('Notify SMS template ID'),
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

    $killswitch = \Drupal::state()->get($this->stateKey);
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

    if (\Drupal::state()->get('fsa_notify.collect_send_log_only')) {
      drupal_set_message(t('<strong>Notify debug mode</strong>: alert collecting is enabled but messages are not sent via Notify.'), 'warning');
    }
    $form['collect_send_log_only'] = [
      '#type' => 'checkbox',
      '#title' => t('Debug mode'),
      '#description' => t('Alerts are collected and processed but not sent to subscribers.'),
      '#default_value' => \Drupal::state()->get('fsa_notify.collect_send_log_only'),
      '#weight' => $weight++,
      '#states' => [
        'visible' => [
          ':input[name="status_new"]' => ['checked' => TRUE],
        ],
      ],
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

    $form['stats_heading'] = [
      '#markup' => '<h2>' . t('Statistics') . '</h2>',
      '#weight' => $weight++,
    ];

    $form['last_sent'] = [
      '#type' => 'details',
      '#open' => TRUE,
      '#title' => t('Last sent digests'),
      '#weight' => $weight++,
    ];

    $form['last_sent']['daily'] = [
      '#type' => 'item',
      '#title' => $this->t('Last daily: @date', [
        '@date' => $date_formatter->format(\Drupal::state()->get('fsa_notify.last_daily'), 'custom', 'l, d.m.Y - g:ia'),
      ]),
      '#weight' => $weight++,
    ];

    $form['last_sent']['weekly'] = [
      '#type' => 'item',
      '#title' => $this->t('Last weekly: @date', [
        '@date' => $date_formatter->format(\Drupal::state()->get('fsa_notify.last_weekly'), 'custom', 'l, d.m.Y - g:ia'),
      ]),
      '#weight' => $weight++,
    ];

    $form['users'] = [
      '#type' => 'details',
      '#open' => TRUE,
      '#title' => t('Users'),
      '#weight' => $weight++,
    ];

    $query = \Drupal::entityQuery('user');
    $query->condition('uid', 0, '>');
    $query->condition('status', 1);
    $query->count();
    $count = $query->execute();
    $form['users']['user_status_enabled'] = [
      '#type' => 'item',
      '#title' => $this->formatPlural($count, '1 enabled user', '@count enabled users'),
      '#weight' => $weight++,
    ];

    $query = \Drupal::entityQuery('user');
    $query->condition('uid', 0, '>');
    $query->condition('status', 0);
    $query->count();
    $count = $query->execute();
    $form['users']['user_status_disabled'] = [
      '#type' => 'item',
      '#title' => $this->formatPlural($count, '1 disabled user', '@count disabled users'),
      '#weight' => $weight++,
    ];

    $entityFieldManager = \Drupal::service('entity_field.manager');
    $fields = $entityFieldManager->getFieldStorageDefinitions('user', 'user');

    $stats_fields = [
      'field_delivery_method' => t('Allergy alert delivery method'),
      'field_delivery_method_news' => t('News and consultation delivery method'),
      'field_email_frequency' => t('EMail frequency'),
    ];
    foreach ($stats_fields as $key => $name) {
      $form[$key] = $this->fsaNotifyStatsDisplay($fields, $key, $name, $weight++);
    }

    return $form;
  }

  /**
   * Pull user preferences for stats.
   *
   * @param object $fields
   *   Fields storagedefinitions.
   * @param string $field
   *   NAme of the field to use in query.
   * @param string $name
   *   Human readable name to print out.
   * @param int $weight
   *   Desired weight of the form element.
   *
   * @return array
   *   FAPI form element to display user stats.
   */
  protected function fsaNotifyStatsDisplay($fields, $field, $name, $weight) {

    if (!isset($fields[$field])) {
      return [];
    }
    $wrapper = str_replace('field_', '', $field);
    $form = [
      '#type' => 'details',
      '#open' => TRUE,
      '#title' => $name . ' (' . $field . ')',
      '#weight' => $weight,
    ];

    $methods = options_allowed_values($fields[$field]);
    foreach ($methods as $key => $description) {
      $query = \Drupal::entityQuery('user');
      $query->condition('uid', 0, '>');
      $query->condition('status', 1);
      $query->condition($field, $key);
      $query->count();
      $count = $query->execute();
      if (empty($count)) {
        $count = t('none');
      }
      $form[$wrapper][$key] = [
        '#type' => 'item',
        '#title' => $description,
        '#plain_text' => $count,
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
      'fsa_notify.template_email_cy',
      'fsa_notify.template_sms',
    ];

    // @todo: Use configuration instead of states and come up with a procedure that by default prevent other than production sending real alerts via Notify API.
    // Currently that risk is mitigated on syncdb.sh with drush sset...
    foreach ($keys as $key) {
      // Cannot have dot in render array key.
      $key2 = str_replace('.', '___', $key);
      $value = $form_state->getValue($key2);
      \Drupal::state()->set($key, $value);
    }

    $status_old = $form_state->getValue('status_old');
    $status_new = $form_state->getValue('status_new');

    if (empty($status_new)) {
      \Drupal::state()->delete($this->stateKey);
    }
    else {
      \Drupal::state()->set($this->stateKey, 1);
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

    if (empty($form_state->getValue('collect_send_log_only'))) {
      \Drupal::state()->delete('fsa_notify.collect_send_log_only');
    }
    else {
      \Drupal::state()->set('fsa_notify.collect_send_log_only', 1);
    }

    // Let the user know something happened.
    drupal_set_message($this->t('Notify settings updated.'));

  }

}
