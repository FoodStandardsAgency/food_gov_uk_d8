<?php

namespace Drupal\fsa_signin\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\fsa_signin\Controller\DefaultController;
use Drupal\fsa_signin\SignInService;
use Drupal\user\Entity\User;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class NewsForRegistrationForm.
 */
class NewsForRegistrationForm extends FormBase {

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
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'news_for_registration_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['#attributes']['class'][] = DefaultController::PROFILE_FORM_HTML_CLASS;

    /** @var \Drupal\user\Entity\User $user */
    $user = User::load(\Drupal::currentUser()->id());

    $news_defaults = [];
    foreach ($user->get('field_subscribed_news')->getValue() as $value) {
      $news_defaults[] = array_shift($value);
    }
    $cons_defaults = [];
    foreach ($user->get('field_subscribed_cons')->getValue() as $value) {
      $cons_defaults[] = array_shift($value);
    }

    $form['beta_description'] = [
      '#markup' => DefaultController::betaSigninDescription(),
    ];
    $form['title'] = [
      '#markup' => '<h2>' . $this->t('News and consultations') . '</h2>',
    ];
    $form['description'] = [
      '#markup' => '<p>' . $this->t("Stay up to date with the FSA's latest news and consultations by email.") . '</p>',
    ];
    $form['news_tids_for_registration'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('News'),
      '#options' => ['all' => $this->t('All news')->render()] + $this->signInService->newsAsOptions(),
      '#default_value' => $news_defaults,
    ];
    $form['cons_tids_for_registration'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Consultations'),
      '#options' => ['all' => $this->t('All consultations')->render()] + $this->signInService->consultationsAsOptions(),
      '#default_value' => $cons_defaults,
    ];
    $form['actions'] = ['#type' => 'actions'];
    $form['actions']['back'] = [
      '#markup' => DefaultController::linkMarkup('fsa_signin.user_preregistration_alerts_form', $this->t('Previous'), ['back arrow']),
    ];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Next'),
    ];
    $form['#attached']['library'][] = 'fsa_signin/subscription_alerts';
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    /** @var \Drupal\user\Entity\User $user */
    $user = User::load(\Drupal::currentUser()->id());

    $news_registration = $form_state->getValue('news_tids_for_registration');
    unset($news_registration['all']);
    // Filter only those user has selected:
    $selected_tids = array_filter(array_values($news_registration));

    $cons_registration = $form_state->getValue('cons_tids_for_registration');
    unset($cons_registration['all']);
    // Filter only those user has selected:
    $selected_cons_tids = array_filter(array_values($cons_registration));

    $user->set('field_subscribed_news', $selected_tids);
    $user->set('field_subscribed_cons', $selected_cons_tids);

    $user->save();
    $form_state->setRedirect('fsa_signin.default_controller_deliveryOptionsPage');
  }

}
