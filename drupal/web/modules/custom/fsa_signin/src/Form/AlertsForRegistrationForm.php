<?php

namespace Drupal\fsa_signin\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\fsa_signin\Controller\DefaultController;
use Drupal\fsa_signin\SignInService;
use Drupal\user\Entity\User;
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

    $form['#attributes']['class'][] = DefaultController::PROFILE_FORM_HTML_CLASS;

    /** @var \Drupal\user\Entity\User $user */
    $user = User::load(\Drupal::currentUser()->id());

    $food_alert_defaults = [];
    foreach ($user->get('field_subscribed_food_alerts')->getValue() as $value) {
      $food_alert_defaults[] = array_shift($value);
    }
    $alert_tid_defaults = [];
    foreach ($user->get('field_subscribed_notifications')->getValue() as $value) {
      $alert_tid_defaults[] = array_shift($value);
    }

    $form['title'] = [
      '#markup' => '<h2>' . $this->t('Food and allergy alerts') . '</h2>',
    ];
    $form['description'] = [
      '#markup' => '<p>' . $this->t("Stay up to date with the FSA's latest food and allergy alerts by email or SMS.") . '</p>',
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
  public function submitForm(array &$form, FormStateInterface $form_state) {
    /** @var \Drupal\user\Entity\User $user */
    $user = User::load(\Drupal::currentUser()->id());

    $food_alert_registration = $form_state->getValue('food_alert_registration');
    $food_alert_registration = array_filter(array_values($food_alert_registration));

    $alert_tids = $form_state->getValue('alert_tids_for_registration');
    // If 'all' is the only selection item, the user may have left this item
    // checked but not have anything else selected. In that case, we should
    // interpret this selection as a desire to receive from all categories
    // so we need to fill the $alert_tids with all term ids from that vocabulary.
    $has_selected_all = !empty($alert_tids['all']);

    unset($alert_tids['all']);
    // Filter only those user has selected:
    $selected_tids = array_filter(array_values($alert_tids));

    if ($has_selected_all) {
      // Fill the $selected_tids array with values (use from the original
      // form element values to avoid reloading taxonomy data) because the
      // user has indicated they want to receive 'All' things.
      $selected_tids = array_keys($form['alert_tids_for_registration']['#options']);

      // 'All' will be index 0.
      unset($selected_tids[0]);
    }

    $user->set('field_subscribed_food_alerts', $food_alert_registration);
    $user->set('field_subscribed_notifications', $selected_tids);

    $user->save();
    $form_state->setRedirect('fsa_signin.user_preregistration_news_form');
  }

}
