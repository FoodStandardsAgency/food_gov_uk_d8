<?php

namespace Drupal\fsa_signin\Form;

use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\fsa_signin\Controller\DefaultController;
use Drupal\user\Entity\User;
use Drupal\fsa_signin\Event\UserCancelEvent;

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
    return new Url('fsa_signin.default_controller_accountSettingsPage');
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
    $message = '<p>' . $this->t('You are about to remove your account with email <i>@email</i>.', ['@email' => $email]) . '</p>';
    $message .= '<p>' . $this->t('Deleting your account will cancel all your subscriptions and permanently remove your personal details from FSA website.') . '</p>';
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
      $markup .= '<p>' . $this->t('If you change your mind you can always <a href="/news-alerts/subscribe">re-subscribe</a>.') . '</p>';

      return [
        '#markup' => $markup,
      ];
    }

    $user = \Drupal::currentUser();
    if ($user->isAnonymous()) {
      $markup = $this->t('You need to be <a href="/news-alerts/signin">logged in</a> in order to delete your profile.');

      return [
        '#markup' => $markup,
      ];
    }

    if (DefaultController::isMoreThanRegistered($user)) {
      $user_roles = $user->getRoles();
      unset($user_roles[0]);
      $roles = implode(', ', $user_roles);
      $count = count($user_roles);
      // Don't let people with more than just "Authenticated" role to delete
      // their account.
      $form['message'] = [
        '#markup' => $this->formatPlural($count, 'You you cannot delete your profile because you have <pre>@roles</pre> role', 'You you cannot delete your profile because your account has following roles: <pre>@roles</pre>', ['@roles' => $roles]) .
        ' ' . t('<a href="/profile/manage">Back to account settings page</a>'),
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

    // Emit a new event to capture this activity. Could tie in with
    // hook_user_cancel() but we're only interested in activity on this very
    // specific form/interaction point and an event fits with
    // the wider approach in fsa_alerts_monitor.
    $user = User::load($uid);
    \Drupal::service('event_dispatcher')->dispatch('fsa_alerts_monitor.user.cancel', new UserCancelEvent($user));

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
