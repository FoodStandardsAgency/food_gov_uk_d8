<?php

namespace Drupal\fsa_signin\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class SendPasswordEmailForm.
 */
class SendPasswordEmailForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'send_password_email_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['email_address'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Email address'),
      '#maxlength' => 64,
      '#size' => 64,
      '#required' => TRUE,
    ];
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Send'),
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
    $email = $form_state->getValue('email_address');
    $langcode = \Drupal::languageManager()->getCurrentLanguage()->getId();
    // Try to load user account by email.
    $users = \Drupal::entityTypeManager()->getStorage('user')->loadByProperties(['mail' => $email]);
    $account = reset($users);
    if (!empty($account)) {
      // Mail one time login URL and instructions using current language.
      $mail = _user_mail_notify('password_reset', $account, $langcode);
      drupal_set_message('An email with instructions was sent to you.', 'status');
    }
    else {
      drupal_set_message('No account with the given email was found.', 'error');
    }
  }

}
