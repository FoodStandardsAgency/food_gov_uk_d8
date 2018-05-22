<?php

namespace Drupal\fsa_signin\Controller;

use Drupal\Core\Link;
use Drupal\Core\Session\AccountInterface;
use Drupal\fsa_signin\Form\ProfileManager;
use Drupal\fsa_signin\Form\SendPasswordEmailForm;
use Drupal\user\Form\UserLoginForm;
use Drupal\Core\Controller\ControllerBase;
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
    // If coming from /user add a signing button even though the form is exactly
    // the same. This for UX separation of subscribers and FSA editors.
    $title = self::linkMarkup('fsa_signin.default_controller_signInPage', t('Alert subscription signin'), ['button']) . '<h2>' . $this->t('User log in') . '</h2>';
    $body = FALSE;
    if (\Drupal::request()->query->get('user') != 'fsa') {
      $title = '<h2>' . $this->t('Sign in or manage your subscription') . '</h2>';
      $body = self::betaSigninDescription();
    }

    $content = ['#markup' => $title . $body];

    $login_form = \Drupal::formBuilder()->getForm(UserLoginForm::class);

    return [
      $content,
      $login_form,
    ];
  }

  /**
   * Create password reset page.
   */
  public function resetPassword() {
    $back = [
      '#markup' => self::linkMarkup(
        'fsa_signin.default_controller_signInPage',
        $this->t('Back'),
        ['back arrow']
      ),
    ];
    $title = ['#markup' => '<h2>' . $this->t('Use one-time sign in') . '</h2>'];
    $content = ['#markup' => '<p>' . $this->t("Enter your email address below and we'll send you a one-time sign in link") . '</p>'];
    $send_pwd_form = \Drupal::formBuilder()->getForm(SendPasswordEmailForm::class);

    return [
      $back,
      $title,
      $content,
      $send_pwd_form,
    ];
  }

  /**
   * Begin the subscription flow.
   */
  public function beginSubscribe() {
    // @see Drupal\fsa_signin\Routing\RouteSubscriber where user is redirected.
    return ['#markup' => 'Registration landing page'];
  }

  /**
   * Create manage profile page.
   */
  public function profilePage() {
    $uid = \Drupal::currentUser()->id();
    $account = User::load($uid);

    $header = '<header class="profile__header">';
    $header .= '<h2 class="profile__heading">' . $this->t('Your profile') . '</h2>';
    $header .= '<p class="profile__intro">' . $this->t("Hello @name", ['@name' => $account->getUsername()]) . '</p>';
    $header .= '</header>';
    $header .= self::linkMarkup('fsa_signin.default_controller_manageProfilePage', $this->t('Manage your profile'), ['button']);

    return [
      ['#markup' => $header],
    ];
  }

  /**
   * Create manage profile page.
   */
  public function manageProfilePage() {
    $header = '<header class="profile__header">';
    $header .= '<h2 class="profile__heading">' . $this->t('Delivery options') . '</h2>';
    $header .= '</header>';
    $header .= '<p class="profile__intro">' . $this->t("Update your subscription or unsubscribe from the alerts you're receiving") . '</p>';

    $manage_form = \Drupal::formBuilder()->getForm(ProfileManager::class);

    return [
      ['#markup' => $header],
      $manage_form,
    ];

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
   * Registration thank you page.
   */
  public function thankYouPage() {
    $markup = '<h1>' . $this->t('A verification email has been sent to your inbox.') . '</h1>';
    $markup .= '<p>' . $this->t('Please check your email and click on the one-time verification link within the mail. If you do not see the email, please check your spam folder.') . '</p>';
    $markup .= '<p>' . self::betaSigninDescription() . '</p>';

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
  public static function isMoreThanRegistered(AccountInterface $user) {
    return count($user->getRoles()) > 1;
  }

  /**
   * Short text to be displayed on subscription flow pages.
   *
   * @param string $version
   *   The version of text (short|long)
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup|string
   *   Translatable text.
   */
  public static function betaSigninDescription($version = 'short') {
    switch ($version) {
      case 'long':
        $description = t('This is a new beta service. Which means you’re looking at the first version of our new service. <a href="/node/724" target="_blank">What this means for you</a>.') . '<p></p>';
        break;

      default:
        $description = '<!-- no description set -->';
        break;
    }

    return $description;
  }

}
