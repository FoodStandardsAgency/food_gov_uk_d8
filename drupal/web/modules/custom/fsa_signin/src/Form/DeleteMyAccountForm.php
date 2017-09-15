<?php

namespace Drupal\fsa_signin\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\user\Entity\User;

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
  public function buildForm(array $form, FormStateInterface $form_state, User $account = NULL) {
    if (count(\Drupal::currentUser()->getRoles()) > 1) {
      $form['message'] = [
        '#markup' => '<p><strong>' . $this->t('This functionality is not available for users with multiple roles.') . '</strong></p>',
      ];
    }
    else {
      $form['account'] = [
        '#type' => 'value',
        '#value' => $account,
      ];
      $form['actions'] = array('#type' => 'actions');
      $form['actions']['submit'] = array(
        '#type' => 'submit',
        '#value' => $this->t('Delete account'),
        '#attributes' => ['class' => ['warning red']],
      );
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
    /** @var \Drupal\user\Entity\User $account */
    $account = $form_state->getValue('account');
    $account->delete();
    drupal_set_message($this->t('Your account has been deleted.'));
    $form_state->setRedirect('<front>');
  }

}
