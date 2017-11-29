<?php

namespace Drupal\fsa_signin\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\fsa_signin\Controller\DefaultController;
use Drupal\user\Entity\User;

/**
 * Class EmailSubscriptionsForm.
 */
class EmailSubscriptionsForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'email_subscriptions_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, User $account = NULL, $options = [], $default_values = []) {
    $form['account'] = [
      '#type' => 'value',
      '#value' => $account,
    ];
    $form['subscribed_food_alerts'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Food alerts'),
      '#options' => $options['subscribed_food_alerts'],
      '#default_value' => $default_values['subscribed_food_alerts'],
    ];
    $form['subscribed_notifications'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Subscribed notifications'),
      '#options' => $options['subscribed_notifications'],
      '#default_value' => $default_values['subscribed_notifications'],
    ];
    $form['actions'] = ['#type' => 'actions'];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save'),
    ];
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
    $subscribed_food_alerts = DefaultController::storableProfileFieldValue($form_state->getValue('subscribed_food_alerts'));
    $subscribed_notifications = $form_state->getValue('subscribed_notifications');

    $account->set('field_subscribed_food_alerts', $subscribed_food_alerts);
    $account->set('field_subscribed_notifications', $subscribed_notifications);
    $account->save();
  }

}
