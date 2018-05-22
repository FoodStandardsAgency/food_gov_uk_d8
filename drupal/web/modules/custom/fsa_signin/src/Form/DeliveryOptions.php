<?php

namespace Drupal\fsa_signin\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\fsa_custom\FsaCustomHelper;
use Drupal\fsa_signin\Controller\DefaultController;
use Drupal\user\Entity\User;
use Drupal\fsa_signin\SignInService;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class DeliveryOptions.
 */
class DeliveryOptions extends FormBase {

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
    return 'delivery_options';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    /** @var \Drupal\user\Entity\User $account */
    $account = User::load(\Drupal::currentUser()->id());

    $form['delivery']['delivery_method'] = [
      '#title' => $this->t('I want to receive food and allergy alerts via'),
      '#type' => 'checkboxes',
      '#options' => [
        'email' => $this->t('Email'),
        'sms' => $this->t('SMS'),
      ],
      '#default_value' => array_column($account->get('field_delivery_method')->getValue(), 'value'),
    ];
    $form['delivery']['delivery_method_news'] = [
      '#title' => $this->t('I want to receive news and consultations via'),
      '#type' => 'checkboxes',
      '#options' => [
        'email' => $this->t('Email'),
      ],
      '#default_value' => array_column($account->get('field_delivery_method_news')->getValue(), 'value'),
    ];
    $form['delivery']['frequency'] = [
      '#type' => 'item',
      '#markup' => '<h3>' . $this->t('Frequency') . '</h3>',
    ];
    $form['delivery']['sms_notification_delivery'] = [
      '#type' => 'checkboxes',
      '#options' => [],
      '#title' => $this->t('SMS frequency'),
      '#markup' => '<p>' . $this->t('SMS updates are sent immediately') . '</p>',
    ];

    // field_email_frequency is a new field replacing old combined
    // field_notification_method hence can be empty on some users. Set default
    // in case is empty.
    $email_frequency = $account->get('field_email_frequency')->getString();
    $email_frequency = ($email_frequency) ? $email_frequency : 'immediate';
    $form['delivery']['email_frequency'] = [
      '#type' => 'radios',
      '#title' => $this->t('Email frequency'),
      '#required' => TRUE,
      '#options' => [
        'immediate' => $this->t('Send updates immediately'),
        'daily' => $this->t('Send updates daily'),
        'weekly' => $this->t('Send updates weekly'),
      ],
      '#default_value' => $email_frequency,
    ];
    $form['delivery']['personal_info'] = [
      '#type' => 'item',
      '#markup' => '<h3>' . $this->t('Personal information') . '</h3>',
    ];
    $form['delivery']['email'] = [
      '#type' => 'email',
      '#title' => $this->t('Email address'),
      '#default_value' => $account->getEmail(),
    ];
    $form['delivery']['phone'] = [
      '#type' => 'tel',
      '#title' => $this->t('Phone number'),
      '#default_value' => $account->get('field_notification_sms')->getString(),
      '#description' => $this->t('This service is only for UK telephone numbers'),
      '#field_prefix' => '+' . SignInService::DEFAULT_COUNTRY_CODE,
      '#states' => [
        'visible' => [
          ':input[name="delivery_method[sms]"]' => ['checked' => TRUE],
        ],
      ],
    ];

    $form['profile']['language'] = [
      '#type' => 'radios',
      '#title' => $this->t('Language preference'),
      '#options' => [
        'en' => $this->t('English'),
        'cy' => $this->t('Cymraeg'),
      ],
      '#default_value' => $account->getPreferredLangcode(),
    ];
    $form['extra']['privacy_notice'] = [
      '#type' => 'item',
      '#markup' => FsaCustomHelper::privacyNoticeLink('alerts'),
    ];

    // Submit and other actions.
    $form['actions'] = ['#type' => 'actions'];
    $form['actions']['submit_edit'] = [
      '#type' => 'button',
      '#executes_submit_callback' => TRUE,
      '#value' => $this->t('Save your changes'),
    ];
//    $form['actions']['delete'] = [
//      '#markup' => DefaultController::linkMarkup('fsa_signin.delete_account_confirmation', $this->t('Cancel your subscription'), ['button cancel']),
//    ];
    // Attach js for the "select all" feature.
    $form['#attached']['library'][] = 'fsa_signin/subscription_alerts';
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $email = $form_state->getValue('email');
    if (!\Drupal::service('email.validator')->isValid($email)) {
      $form_state->setErrorByName('email', $this->t('Email value is not valid.'));
    }

    $delivery_method = $form_state->getValue('delivery_method');
    $delivery_method = array_filter(array_values($delivery_method));
    if (in_array('sms', $delivery_method)) {
      $phone = $form_state->getValue('phone');

      if (!preg_match('/^[0-9 ]{0,}$/', $phone)) {
        $form_state->setErrorByName('phone', $this->t('Special characters are not allowed in phone number.'));
      }
      elseif (!preg_match('/^[0-9 ]{8,}$/', $phone)) {
        $form_state->setErrorByName('phone', $this->t('Phone number appears to be too short.'));
      }
      if ($phone == '') {
        $form_state->setErrorByName('phone', $this->t('You selected to receive alerts via SMS, please enter your phone number.'));
      }
    }

  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    /** @var \Drupal\user\Entity\User $account */
    $account = User::load(\Drupal::currentUser()->id());

    $email = $form_state->getValue('email');
    $account->setEmail($email);

    $delivery_method = $form_state->getValue('delivery_method');
    $delivery_method = array_filter(array_values($delivery_method));
    $account->set('field_delivery_method', $delivery_method);

    $delivery_method_news = $form_state->getValue('delivery_method_news');
    $delivery_method_news = array_filter(array_values($delivery_method_news));
    $account->set('field_delivery_method_news', $delivery_method_news);

    // Store phone without spaces and remove countrycode if it was entered.
    $phone = ltrim(str_replace(' ', '', $form_state->getValue('phone')), SignInService::DEFAULT_COUNTRY_CODE);
    if (in_array('sms', $delivery_method)) {
      // Only store the phone number if user subscribed via SMS.
      $account->set('field_notification_sms', $phone);
    }
    else {
      $account->set('field_notification_sms', '');
    }

    $email_frequency = $form_state->getValue('email_frequency');
    $account->set('field_email_frequency', $email_frequency);

    $language = $form_state->getValue('language');
    $account->set('preferred_langcode', $language);

    if ($account->save()) {
      drupal_set_message($this->t('Your preferences are updated.'));
    }
    else {
      drupal_set_message($this->t('There was an error updating your preferences. Please try again.'));
    }
  }

}
