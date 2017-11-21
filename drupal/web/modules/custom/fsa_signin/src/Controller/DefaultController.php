<?php

namespace Drupal\fsa_signin\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Drupal\fsa_signin\SignInService;
use Drupal\taxonomy\Entity\Term;
use Drupal\user\Entity\User;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class DefaultController.
 */
class DefaultController extends ControllerBase {

  /** @var  SignInService */
  protected $signInService;

  /**
   * {@inheritdoc}
   */
  public function __construct(SignInService $signInService) {
    $this->signInService = $signInService;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('fsa_signin.service')
    );
  }

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
    $subscribed_term_ids = $this->signInService->subscribedTermIds($account);
    $options = $this->signInService->allergenTermsAsOptions();

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
    $subscribed_term_ids = $this->signInService->subscribedTermIds($account);
    $options = $this->signInService->allergenTermsAsOptions();

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

  public function thankYouPage() {
    $profile_page_url = Url::fromRoute('fsa_signin.default_controller_myAccountPage')->toString();
    $markup  = '<h1>' . $this->t('Thank you!') . '</h1>';
    $markup .= '<p>' . $this->t('Edit your subscriptions in your account page') . '</p>';
    $markup .= '<p><a class="button" href="' .$profile_page_url. '">' . $this->t('My account') . '</a></p>';

    return [
      '#markup' => $markup,
    ];
  }

  /**
   * Check if user is more than just authenticated user (alert subscriber).
   *
   * @return bool
   *   TRUE if user has more than one role
   */
  public static function isMoreThanRegistered($user) {
    return count($user->getRoles()) > 1;
  }

}
