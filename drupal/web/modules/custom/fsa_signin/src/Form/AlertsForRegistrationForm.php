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
    $default_values = $tempstore->get('alert_tids_for_registration');
    $options = array_merge(['all' => $this->t('All allergy alerts')], $this->signInService->allergenTermsAsOptions());

    $form['alert_tids_for_registration'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Allergy alerts'),
      '#options' => $options,
      '#default_value' => $default_values,
      '#description' => $this->t('Select all that apply'),
    ];
    $form['actions'] = array('#type' => 'actions');
    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Next'),
    );
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
      $_SESSION['multistep_form_holds_session'] = true;
      \Drupal::service('session_manager')->start();
    }

    $alert_tids = $form_state->getValue('alert_tids_for_registration');
    // Filter only those user has selected:
    $selected_tids = array_filter(array_values($alert_tids));

    /** @var \Drupal\user\PrivateTempStore $tempstore */
    $tempstore = \Drupal::service('user.private_tempstore')->get('fsa_signin');
    $tempstore->set('alert_tids_for_registration', $selected_tids);
    $form_state->setRedirect('fsa_signin.user_registration_form');
  }

}
