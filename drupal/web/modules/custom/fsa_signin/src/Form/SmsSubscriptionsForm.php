<?php

namespace Drupal\fsa_signin\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\user\Entity\User;

/**
 * Class SmsSubscriptionsForm.
 */
class SmsSubscriptionsForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'sms_subscriptions_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, User $account = NULL, $options = [], $default_values = []) {
    $form['account'] = [
      '#type' => 'value',
      '#value' => $account,
    ];
    $form['subscribed_notifications'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Subscribed notifications'),
      '#options' => $options,
      '#default_value' => $default_values,
    ];
    $form['actions'] = array('#type' => 'actions');
    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Save'),
    );
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {

  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    /** @var \Drupal\user\Entity\User $account */
    $account = $form_state->getValue('account');
    $subscribed_notifications = $form_state->getValue('subscribed_notifications');
    $account->set('field_subscribed_notifications', $subscribed_notifications);
    $account->save();
  }

}
