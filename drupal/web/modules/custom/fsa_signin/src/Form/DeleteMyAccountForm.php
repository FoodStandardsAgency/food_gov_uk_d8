<?php

namespace Drupal\fsa_signin\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\fsa_signin\Controller\DefaultController;

/**
 * Class DeleteMyAccountForm.
 */
class DeleteMyAccountForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'delete_my_account_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $user = \Drupal::currentUser();
    if (DefaultController::isMoreThanRegistered($user)) {
      $form['message'] = [
        '#markup' => '<p><strong>' . $this->t('This functionality is not available for users with multiple roles.') . '</strong></p>',
      ];
    }
    else {
      $form['actions'] = ['#type' => 'actions'];
      $form['actions']['submit'] = [
        '#type' => 'submit',
        '#value' => $this->t('Delete account'),
        '#attributes' => ['class' => ['warning red']],
      ];
    }
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
    $form_state->setRedirect('fsa_signin.delete_account_confirmation');
  }

}
