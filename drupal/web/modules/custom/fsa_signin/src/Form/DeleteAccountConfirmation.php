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
    return t('Delete account');
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('fsa_signin.default_controller_manageProfilePage');
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {

    $privacy_nid = \Drupal::state()->get('fsa_custom.privacy_link_nid');
    if (is_numeric($privacy_nid)) {
      $privacy_link = DefaultController::linkMarkup('entity.node.canonical', $this->t('Privacy notice'), [], ['node' => $privacy_nid]);
    }
    else {
      $privacy_link = '<pre>[Privacy page link not defined]</pre>';
    }
    $user = \Drupal::currentUser();
    $email = $user->getEmail();
    $message = '<p>' . $this->t('You are about to remove subscription with email <strong>@email</strong>.', ['@email' => $email]) . '</p>';
    $message .= '<p>' . $this->t('This will cancel all your subscriptions and permanently remove your personal details.') . '</p>';
    $message .= '<p>' . $privacy_link . '</p>';
    return $message;
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('Remove your profile');
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

    if (\Drupal::request()->query->has('done')) {
      $markup = '<p>' . $this->t('Your profile has been successfully removed.') . '</p>';

      return [
        '#markup' => $markup,
      ];
    }

    $user = \Drupal::currentUser();
    if ($user->isAnonymous()) {
      $markup = $this->t('Log in or create account');

      return [
        '#markup' => $markup,
      ];
    }

    $form['back'] = [
      '#prefix' => '<header class="profile__header">',
      '#markup' => DefaultController::linkMarkup('fsa_signin.default_controller_manageProfilePage', $this->t('Back'), ['back']),
    ];
    $form['logout'] = [
      '#suffix' => '</header>',
      '#markup' => DefaultController::linkMarkup('user.logout.http', $this->t('Logout'), ['profile__logout']),
    ];

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
    $uid = $account->id();
    $email = $account->getEmail();

    // "Anonymise the email.
    $email = '***' . strstr($email, '@');

    // Permanently delete the account.
    $account->delete();

    \Drupal::logger('fsa_signing')->notice(
      t(
        'User @id with email @email self-deleted their account.',
        [
          '@id' => $uid,
          '@email' => $email,
        ]
      )
    );

    $form_state->setRedirect('fsa_signin.delete_account_confirmation', [], ['query' => ['done' => NULL]]);
  }

}
