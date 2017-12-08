<?php

namespace Drupal\fsa_signin\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\user\Entity\User;

/**
 * Class MyAccountForm.
 */
class MyAccountForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'my_account_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, User $account = NULL) {
    $form['account'] = [
      '#type' => 'value',
      '#value' => $account,
    ];
    $form['new_password'] = [
      '#type' => 'password_confirm',
      '#title' => $this->t('New password'),
    ];
    $form['actions'] = ['#type' => 'actions'];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Update'),
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {

    $password = $form_state->getValue('new_password');
    if (!$password) {
      $form_state->setErrorByName(
        'new_password',
        $this->t('Please enter a password to change it.')
      );
    }

    $length = 5;
    if (strlen($password) < $length) {
      $form_state->setErrorByName(
        'new_password',
        $this->t('Password not updated: Please use a password of @length or more characters.',
          ['@length' => $length]
        )
      );
    }

  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    /** @var \Drupal\user\Entity\User $account */
    $account = $form_state->getValue('account');
    $pass = $form_state->getValue('new_password');
    $account->setPassword($pass);
    $account->save();
    drupal_set_message($this->t('Your password is now updated.'));
  }

}
