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

    $form['state'] = [
      '#type' => 'item',
      '#plain_text' => t('Following state settings can be set by: drush state-set key value'),
    ];

    $keys = [
      'fsa_notify.api',
      'fsa_notify.template_email',
      'fsa_notify.template_sms',
    ];

    foreach ($keys as $key) {
      $render = [
        '#type' => 'item',
        '#title' => $key,
      ];
      $value = \Drupal::state()->get($key);
      if (empty($value)) {
        $value = t('Please get value from https://www.notifications.service.gov.uk/');
        $value = sprintf('<i>%s</i>', $value);
        $render['#markup'] = $value;
      }
      else {
        $render['#plain_text'] = $value;
      }
      $form[$key] = $render;
    }

    $killswitch = \Drupal::state()->get($this->state_key);
    $killswitch = (bool) $killswitch;

    $form['old'] = [
      '#type' => 'value',
      '#value' => $killswitch,
    ];

    $form['status'] = [
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

    $old = $form_state->getValue('old');
    $status = $form_state->getValue('status');

    if (empty($status)) {
      \Drupal::state()->delete($this->state_key);
    } else {
      \Drupal::state()->set($this->state_key, 1);
    }

    if (empty($old) && !empty($status)) {
      drupal_set_message(t('Notification system is now ENABLED.'));
    }

    if (!empty($old) && empty($status)) {
      drupal_set_message(t('Notification system is now DISABLED.'));
    }

  }

}
