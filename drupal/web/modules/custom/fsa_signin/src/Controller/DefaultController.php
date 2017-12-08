<?php

namespace Drupal\fsa_signin\Controller;

use Drupal\Core\Link;
use Drupal\Core\Routing\RouteProvider;
use Drupal\fsa_signin\Form\DeleteMyAccountForm;
use Drupal\fsa_signin\Form\MyAccountForm;
use Drupal\fsa_signin\Form\ProfileManager;
use Drupal\fsa_signin\Form\SmsSubscriptionsForm;
use Drupal\fsa_signin\Form\EmailPreferencesForm;
use Drupal\fsa_signin\Form\EmailSubscriptionsForm;
use Drupal\fsa_signin\Form\SendPasswordEmailForm;
use Drupal\fsa_signin\Form\CtaRegister;
use Drupal\user\Form\UserLoginForm;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Drupal\fsa_signin\SignInService;
use Drupal\user\Entity\User;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\fsa_signin\Form\UnsubscribeForm;

/**
 * Class DefaultController.
 */
class DefaultController extends ControllerBase {

  /**
   * Signin service.
   *
   * @var \Drupal\fsa_signin\SignInService
   */
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

  /**
   * Create signin page.
   */
  public function signInPage() {
    $title = ['#markup' => '<h1>' . $this->t('Sign in') . '</h1>'];
    $login_form = \Drupal::formBuilder()->getForm(UserLoginForm::class);
    $cta_register_form = \Drupal::formBuilder()->getForm(CtaRegister::class);
    $send_pwd_form = \Drupal::formBuilder()->getForm(SendPasswordEmailForm::class);

    return [
      $title,
      $login_form,
      $cta_register_form,
      $send_pwd_form,
    ];
  }

  /**
   * Create manage profile page.
   */
  public function manageProfilePage() {
    $uid = \Drupal::currentUser()->id();
    $account = User::load($uid);

    $options = [
      'subscribed_notifications' => $this->signInService->allergenTermsAsOptions(),
      'subscribed_food_alerts' => $this->signInService->foodAlertsAsOptions(),
    ];

    $default_values = [
      'subscribed_food_alerts' => $this->signInService->subscribedFoodAlerts($account),
      'subscribed_notifications' => $this->signInService->subscribedTermIds($account),
    ];

    $header = '<h2>' . $this->t('Manage your preferences') . '</h2>';
    $header .= '<p>' . $this->t("Update your subscription or unsubscribe from the alerts you're receiving") . '</p>';
    $header .= self::linkMarkup('user.logout.http', 'Logout', ['button']);
    $manage_form = \Drupal::formBuilder()->getForm(ProfileManager::class, $account, $options, $default_values);

    return [
      ['#markup' => $header],
      $manage_form,
    ];

    /*
    $options = [
      'subscribed_notifications' => $this->signInService->allergenTermsAsOptions(),
      'subscribed_food_alerts' => $this->signInService->foodAlertsAsOptions(),
    ];

    $default_values = [
      'subscribed_food_alerts' => $this->signInService->subscribedFoodAlerts($account),
      'subscribed_notifications' => $this->signInService->subscribedTermIds($account),
    ];

    $subscription_form = \Drupal::formBuilder()->getForm(EmailSubscriptionsForm::class, $account, $options, $default_values);
    $preferences_form = \Drupal::formBuilder()->getForm(EmailPreferencesForm::class, $account);

    $acc_form = \Drupal::formBuilder()->getForm(MyAccountForm::class, $account);
    $delete_acc_form = \Drupal::formBuilder()->getForm(DeleteMyAccountForm::class, $account);

    return [
      ['#markup' => '<div class="profile header subscriptions"><h2>' . $this->t('Subscriptions') . '</h2></div>'],
      $subscription_form,
      ['#markup' => '<div class="profile header preferences"><h2>' . $this->t('Preferences') . '</h2></div>'],
      $preferences_form,
      ['#markup' => '<div class="profile header password"><h2>' . $this->t('Password') . '</h2></div>'],
      $acc_form,
      ['#markup' => '<div class="profile header account-delete"><h2>' . $this->t('Delete account') . '</h2></div>'],
      ['#markup' => '<p>' . $this->t('Unsubscribe from all topics delivered by email and SMS and delete your account below. This operation cannot be undone.') . '</p>'],
      $delete_acc_form,
    ];
*/
  }

  /**
   * Create register page.
   */
  public function registerPage() {
    $entity = \Drupal::entityTypeManager()->getStorage('user')->create([]);
    $formObject = \Drupal::entityTypeManager()
      ->getFormObject('user', 'register')
      ->setEntity($entity);
    $register_form = \Drupal::formBuilder()->getForm($formObject);

    return $register_form;
  }

  /**
   * Create email subscription pref. page.
   */
  public function emailSubscriptionsPage() {
    $uid = \Drupal::currentUser()->id();
    $account = User::load($uid);

    $options = [
      'subscribed_notifications' => $this->signInService->allergenTermsAsOptions(),
      'subscribed_food_alerts' => $this->signInService->foodAlertsAsOptions(),
    ];

    $default_values = [
      'subscribed_food_alerts' => $this->signInService->subscribedFoodAlerts($account),
      'subscribed_notifications' => $this->signInService->subscribedTermIds($account),
    ];

    $subscription_form = \Drupal::formBuilder()->getForm(EmailSubscriptionsForm::class, $account, $options, $default_values);
    $preferences_form = \Drupal::formBuilder()->getForm(EmailPreferencesForm::class, $account);

    return [
      ['#markup' => '<div class="profile header subscriptions"><h2>' . $this->t('Subscriptions') . '</h2></div>'],
      $subscription_form,
      ['#markup' => '<div class="profile header preferences"><h2>' . $this->t('Preferences') . '</h2></div>'],
      $preferences_form,
    ];
  }

  /**
   * Create SMS subscription pref. page.
   */
  public function smsSubscriptionsPage() {
    $uid = \Drupal::currentUser()->id();
    $account = User::load($uid);
    $subscribed_term_ids = $this->signInService->subscribedTermIds($account);
    $options = $this->signInService->allergenTermsAsOptions();

    $subscription_form = \Drupal::formBuilder()->getForm(SmsSubscriptionsForm::class, $account, $options, $subscribed_term_ids);

    return [
      ['#markup' => '<div class="profile header subscriptions"><h2>' . $this->t('Subscriptions') . '</h2></div>'],
      $subscription_form,
    ];
  }

  /**
   * Creates the account page.
   */
  public function myAccountPage() {
    $uid = \Drupal::currentUser()->id();
    $account = User::load($uid);
    $acc_form = \Drupal::formBuilder()->getForm(MyAccountForm::class, $account);
    $delete_acc_form = \Drupal::formBuilder()->getForm(DeleteMyAccountForm::class, $account);
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
   * Registration thank you page.
   */
  public function thankYouPage() {
    $markup = '<h1>' . $this->t('Subscription complete') . '</h1>';
    $markup .= '<p>' . $this->t("Thank you for subscribing to Food Standards Agency's updates.") . '</p>';
    $markup .= self::linkMarkup('fsa_signin.default_controller_manageProfilePage', 'Manage your preferences', ['button']);

    return [
      '#markup' => $markup,
    ];
  }

  /**
   * Creates unsubscribe page.
   *
   * @return array
   *   Drupal\fsa_signin\Form\UnsubscribeForm.
   */
  public function unsubscribePage() {

    $unsubscribe_form = \Drupal::formBuilder()->getForm(UnsubscribeForm::class);

    return [$unsubscribe_form];
  }

  /**
   * Create backlink for form.
   *
   * @param string $route
   *   Name of the route to link to.
   *
   * @return \Drupal\core\Link
   *   Drupal link.
   *
   * @deprecated Use DefaultController::linkMarkup().
   */
  public static function formBackLink($route) {
    $text = t('Previous');
    $parameters = [];
    $options = [
      'attributes' => [
        'class' => [
          'button',
          'black',
        ],
      ],
    ];

    $link = Link::createFromRoute(
      $text,
      $route,
      $parameters,
      $options);

    return $link;
  }

  /**
   * Link markup for profile navigation.
   *
   * @param string $route_name
   *   Name of the route.
   * @param string $text
   *   Link text.
   * @param array $classes
   *   Array of html classes for the link markup.
   * @param array $route_params
   *   Route parameters.
   * @param string $prefix
   *   Prefix (can be markup)
   * @param string $suffix
   *   Suffix (can be markup)
   *
   * @return string
   *   Link markup.
   */
  public static function linkMarkup($route_name, $text, array $classes = [], array $route_params = [], $prefix = '', $suffix = '') {

    $options = [
      'attributes' => [
        'class' => $classes,
      ],
    ];

    if (\Drupal::routeMatch()->getRouteName() == $route_name) {
      $options['attributes']['class'][] = 'is-active';
    }

    $link_object = Link::createFromRoute($text, $route_name, $route_params, $options);
    return $prefix . $link_object->toString() . $suffix;
  }

  /**
   * Modify form value to be saved correctly.
   *
   * @param array $values
   *   The values array.
   *
   * @return array|mixed
   *   Value to be saved for a field.
   */
  public static function storableProfileFieldValue(array $values) {
    foreach ($values as $key => $value) {
      // @todo: refactor this if we need more food alerts to subscribe to.
      $values = ($value === 0) ? [] : key($values);
    }
    return $values;
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
