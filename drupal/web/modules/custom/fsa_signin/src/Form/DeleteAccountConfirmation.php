<?php

namespace Drupal\fsa_signin\Form;

use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\fsa_signin\Controller\DefaultController;
use Drupal\user\Entity\User;

/**
 * Defines a confirmation form for deleting mymodule data.
 */
class DeleteAccountConfirmation extends ConfirmFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'my_user_delete_form';
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return t('Are you sure you want to delete your account?');
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('fsa_signin.default_controller_myAccountPage');
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return t('Confirm account deletion.');
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('Yes, delete my account');
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelText() {
    return $this->t('Cancel');
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $user = \Drupal::currentUser();
    if (DefaultController::isMoreThanRegistered($user)) {
      // Don't let people with more than just "Authenticated" role to delete
      // their account.
      $form['message'] = [
        '#markup' => '<p><strong>' . $this->t('This functionality is not available for users with multiple roles.') . '</strong></p>',
      ];

      return $form;
    }
    else {
      $account = User::load($user->id());
      $form['account'] = [
        '#type' => 'value',
        '#value' => $account,
      ];

      return parent::buildForm($form, $form_state);
    }

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
