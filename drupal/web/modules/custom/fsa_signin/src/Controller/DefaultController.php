<?php

namespace Drupal\fsa_signin\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\taxonomy\Entity\Term;
use Drupal\user\Entity\User;

/**
 * Class DefaultController.
 */
class DefaultController extends ControllerBase {

  public function signInPage() {
    $title = ['#markup' => '<h1>' . $this->t('Sign in') . '</h1>'];
    $login_form = \Drupal::formBuilder()->getForm(\Drupal\user\Form\UserLoginForm::class);
    $cta_register_form = \Drupal::formBuilder()->getForm(\Drupal\fsa_signin\Form\CtaRegister::class);
    $send_pwd_form = \Drupal::formBuilder()->getForm(\Drupal\fsa_signin\Form\SendPasswordEmailForm::class);

    return [
      $title,
      $login_form,
      $cta_register_form,
      $send_pwd_form
    ];
  }

  public function registerPage() {
    $entity = \Drupal::entityTypeManager()->getStorage('user')->create(array());
    $formObject = \Drupal::entityTypeManager()
      ->getFormObject('user', 'register')
      ->setEntity($entity);
    $register_form = \Drupal::formBuilder()->getForm($formObject);

    return $register_form;
  }

  public function emailSubscriptionsPage() {
    $uid = \Drupal::currentUser()->id();
    $account = User::load($uid);
    $subscribed_term_ids = $this->subscribedTermIds($account);
    $options = $this->allergenTermsAsOptions();

    $subscription_form = \Drupal::formBuilder()->getForm(\Drupal\fsa_signin\Form\EmailSubscriptionsForm::class, $account, $options, $subscribed_term_ids);
    $preferences_form = \Drupal::formBuilder()->getForm(\Drupal\fsa_signin\Form\EmailPreferencesForm::class, $account);

    return [
      ['#markup' => '<div class="profile header subscriptions"><h2>' . $this->t('Subscriptions') . '</h2></div>'],
      $subscription_form,
      ['#markup' => '<div class="profile header preferences"><h2>' . $this->t('Preferences') . '</h2></div>'],
      $preferences_form,
    ];
  }

  public function smsSubscriptionsPage() {
    $uid = \Drupal::currentUser()->id();
    $account = User::load($uid);
    $subscribed_term_ids = $this->subscribedTermIds($account);
    $options = $this->allergenTermsAsOptions();

    $subscription_form = \Drupal::formBuilder()->getForm(\Drupal\fsa_signin\Form\SmsSubscriptionsForm::class, $account, $options, $subscribed_term_ids);

    return [
      ['#markup' => '<div class="profile header subscriptions"><h2>' . $this->t('Subscriptions') . '</h2></div>'],
      $subscription_form,
    ];
  }

  public function myAccountPage() {
    $uid = \Drupal::currentUser()->id();
    $account = User::load($uid);
    $acc_form = \Drupal::formBuilder()->getForm(\Drupal\fsa_signin\Form\MyAccountForm::class, $account);
    $delete_acc_form = \Drupal::formBuilder()->getForm(\Drupal\fsa_signin\Form\DeleteMyAccountForm::class, $account);
    return [
      ['#markup' => '<div class="profile header password"><h2>' . $this->t('Change password') . '</h2></div>'],
      ['#markup' => '<p>' . $this->t('If you would like to change your existing password, please enter it below.') . '</p>'],
      $acc_form,
      ['#markup' => '<div class="profile header account-delete"><h2>' . $this->t('Delete account') . '</h2></div>'],
      ['#markup' => '<p>' . $this->t('Unsubscribe from all topics delivered by email and SMS and delete your account below. This operation cannot be undone.') . '</p>'],
      $delete_acc_form,
    ];
  }

  /**
   * @return array
   */
  protected function allergenTermsAsOptions() {
    $all_terms = \Drupal::entityTypeManager()
      ->getStorage('taxonomy_term')
      ->loadTree('alerts_allergen', 0, 1, FALSE);
    $options = [];
    foreach ($all_terms as $term) {
      $options[$term->tid] = $this->t($term->name);
    }
    return $options;
  }

  /**
   * @param \Drupal\user\Entity\User $account
   * @return int[] Term IDs
   */
  protected function subscribedTermIds(User $account) {
    $subscriptions = $account->get('field_subscribed_notifications')
      ->getValue();
    $subscribed_term_ids = [];
    foreach ($subscriptions as $s) {
      $subscribed_term_ids[] = intval($s['target_id']);
    }
    return $subscribed_term_ids;
  }
}
