<?php

namespace Drupal\fsa_signin\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\user\Entity\User;

/**
 * Class EmailPreferencesForm.
 */
class EmailPreferencesForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'email_preferences_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, User $account = NULL) {
    $form['account'] = [
      '#type' => 'value',
      '#value' => $account,
    ];
    $form['email'] = [
      '#type' => 'email',
      '#default_value' => $account->getEmail(),
    ];
    $form['email_notification_delivery'] = [
      '#type' => 'radios',
      '#title' => $this->t('Email delivery preference'),
      '#options' => [
        'immediate' => $this->t('Send updates immediately by email'),
        'daily' => $this->t('Send updates daily by email'),
        'weekly' => $this->t('Send updates weekly by email'),
      ],
      '#default_value' => $account->get('field_notification_method')->getString(),
    ];
    $form['email_notification_language'] = [
      '#type' => 'radios',
      '#title' => $this->t('Language preference'),
      '#options' => [
        'en' => $this->t('English'),
        'cy' => $this->t('Cymraeg'),
      ],
      '#default_value' => $account->getPreferredLangcode(),
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
    $email = $form_state->getValue('email');
    if (!\Drupal::service('email.validator')->isValid($email)) {
      $form_state->setErrorByName('email', $this->t('Email value is not valid.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    /** @var \Drupal\user\Entity\User $account */
    $account = $form_state->getValue('account');
    $email = $form_state->getValue('email');
    $email_notification_delivery = $form_state->getValue('email_notification_delivery');
    $email_notification_language = $form_state->getValue('email_notification_language');
    $account->setEmail($email);
    $account->set('field_notification_method', $email_notification_delivery);
    $account->set('preferred_langcode', $email_notification_language);
    $account->save();
  }

}
