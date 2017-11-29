<?php

namespace Drupal\fsa_signin\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\fsa_signin\SignInService;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class AlertsForRegistrationForm.
 */
class AlertsForRegistrationForm extends FormBase {

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
    return 'alert_for_registration_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    /** @var \Drupal\user\PrivateTempStore $tempstore */
    $tempstore = \Drupal::service('user.private_tempstore')->get('fsa_signin');
    $alert_tid_defaults = $tempstore->get('alert_tids_for_registration');
    $alert_tid_defaults = ($alert_tid_defaults === NULL) ? [] : $alert_tid_defaults;

    $food_alert_defaults = $tempstore->get('food_alert_registration');
    $food_alert_defaults = ($food_alert_defaults === NULL) ? [] : $food_alert_defaults;

    $form['title'] = [
      '#markup' => '<h2>' . $this->t('Alerts') . '</h2>',
    ];
    $form['description'] = [
      '#markup' => '<p>' . $this->t("Get details of food recalls and withdrawals and allergy alerts as soon as they're issued by email or sent as an SMS text message direct to your mobile phone. This is a free service.") . '</p>',
    ];
    $form['food_alert_registration'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Food alerts'),
      '#options' => $this->signInService->foodAlertsAsOptions(),
      '#default_value' => $food_alert_defaults,
    ];

    $form['alert_tids_for_registration'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Allergy alerts'),
      '#options' => ['all' => $this->t('All allergy alerts')->render()] + $this->signInService->allergenTermsAsOptions(),
      '#default_value' => $alert_tid_defaults,
      '#description' => $this->t('Select all that apply'),
    ];
    $form['actions'] = ['#type' => 'actions'];
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
  public function validateForm(array &$form, FormStateInterface $form_state) {

  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Start a manual session for anonymous users.
    if (\Drupal::currentUser()->isAnonymous() && !isset($_SESSION['multistep_form_holds_session'])) {
      $_SESSION['multistep_form_holds_session'] = TRUE;
      \Drupal::service('session_manager')->start();
    }

    $food_alert_registration = $form_state->getValue('food_alert_registration');

    $alert_tids = $form_state->getValue('alert_tids_for_registration');
    // Filter only those user has selected:
    $selected_tids = array_filter(array_values($alert_tids));

    /** @var \Drupal\user\PrivateTempStore $tempstore */
    $tempstore = \Drupal::service('user.private_tempstore')->get('fsa_signin');
    $tempstore->set('food_alert_registration', $food_alert_registration);
    $tempstore->set('alert_tids_for_registration', $selected_tids);
    $form_state->setRedirect('fsa_signin.user_registration_form');
  }

}
