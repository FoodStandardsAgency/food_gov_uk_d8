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
 * Class ProfileManager.
 */
class ProfileManager extends FormBase {

  const PROFILE_PASSWORD_LENGTH = 8;


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
    return 'profile_manager';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, User $account = NULL) {

    $form['account'] = [
      '#type' => 'value',
      '#value' => $account,
    ];

    // Food and allergy alerts wrapper.
    $is_open = FALSE;
    $wrapper = 'food-allergy';
    $label = $this->t('Food and allergy alerts');
    $form[$wrapper . '_button'] = $this->wrapperButton($wrapper, $label, $is_open);
    $form[$wrapper] = $this->wrapperElement($wrapper, $is_open);
    $form[$wrapper]['subscribed_food_alerts'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Food alerts'),
      '#options' => $this->signInService->foodAlertsAsOptions(),
      '#default_value' => array_column($account->get('field_subscribed_food_alerts')->getValue(), 'value'),
    ];
    $form[$wrapper]['subscribed_allergy_alerts'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Allergy alerts'),
      '#options' => ['all' => $this->t('All allergy alerts')->render()] + $this->signInService->allergenTermsAsOptions(),
      '#default_value' => array_column($account->get('field_subscribed_notifications')->getValue(), 'target_id'),
    ];

    // News and consultations wrapper.
    $is_open = FALSE;
    $wrapper = 'news-consultation';
    $label = $this->t('News and consultations');
    $form[$wrapper . '_button'] = $this->wrapperButton($wrapper, $label, $is_open);
    $form[$wrapper] = $this->wrapperElement($wrapper, $is_open);
    $form[$wrapper]['subscribed_news'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('News'),
      '#options' => ['all' => $this->t('All news')->render()] + $this->signInService->newsAsOptions(),
      '#default_value' => array_column($account->get('field_subscribed_news')->getValue(), 'target_id'),
    ];
    $form[$wrapper]['subscribed_cons'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Consultations'),
      '#options' => ['all' => $this->t('All consultations')->render()] + $this->signInService->consultationsAsOptions(),
      '#default_value' => array_column($account->get('field_subscribed_cons')->getValue(), 'target_id'),
    ];

    // Delivery options wrapper.
    $is_open = FALSE;
    $wrapper = 'delivery';
    $label = $this->t('Delivery options');
    $form[$wrapper . '_button'] = $this->wrapperButton($wrapper, $label, $is_open);
    $form[$wrapper] = $this->wrapperElement($wrapper, $is_open);
    $form[$wrapper]['delivery_method_title'] = [
      '#type' => 'item',
      '#markup' => '<h3>' . $this->t('I want to receive food and allergy alerts via') . '</h3>',
    ];
    $form[$wrapper]['delivery_method'] = [
      '#type' => 'checkboxes',
      '#options' => [
        'email' => $this->t('Email'),
        'sms' => $this->t('SMS'),
      ],
      '#default_value' => array_column($account->get('field_delivery_method')->getValue(), 'value'),
    ];
    $form[$wrapper]['news_notifications'] = [
      '#type' => 'item',
      '#markup' => '<h3>' . $this->t('I want to receive news and consultations via') . '</h3>',
    ];
    $form[$wrapper]['news_notifications_method'] = [
      '#type' => 'item',
      '#markup' => '[not implemented yet]',
    ];
    $form[$wrapper]['frequency'] = [
      '#type' => 'item',
      '#markup' => '<h3>' . $this->t('Frequency') . '</h3>',
    ];
    $form[$wrapper]['sms_notification_delivery'] = [
      '#type' => 'checkboxes',
      '#options' => [],
      '#title' => $this->t('SMS frequency'),
      '#markup' => '<p>' . $this->t('SMS updates are sent immediately') . '</p>',
    ];
    $form[$wrapper]['email_notification_delivery'] = [
      '#type' => 'radios',
      '#title' => $this->t('Email frequency'),
      '#required' => TRUE,
      '#options' => [
        'immediate' => $this->t('Send updates immediately'),
        'daily' => $this->t('Send updates daily'),
        'weekly' => $this->t('Send updates weekly'),
      ],
      '#default_value' => $account->get('field_notification_method')->getString(),
    ];
    $form[$wrapper]['personal_info'] = [
      '#type' => 'item',
      '#markup' => '<h3>' . $this->t('Personal information') . '</h3>',
    ];
    $form[$wrapper]['email'] = [
      '#type' => 'email',
      '#title' => $this->t('Email address'),
      '#default_value' => $account->getEmail(),
    ];
    $form[$wrapper]['phone'] = [
      '#type' => 'tel',
      '#title' => $this->t('Phone number'),
      '#default_value' => $account->get('field_notification_sms')->getString(),
      '#description' => $this->t('This service is only for UK telephone numbers'),
      '#states' => [
        'visible' => [
          ':input[name="delivery_method[sms]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $form[$wrapper]['email_notification_language'] = [
      '#type' => 'radios',
      '#title' => $this->t('Language preference'),
      '#options' => [
        'en' => $this->t('English'),
        'cy' => $this->t('Cymraeg'),
      ],
      '#default_value' => $account->getPreferredLangcode(),
    ];
    $form[$wrapper]['privacy_notice'] = [
      '#type' => 'item',
      '#markup' => FsaCustomHelper::privacyNoticeLink('alerts'),
    ];

    // Password wrapper.
    $is_open = FALSE;
    $wrapper = 'password';
    $label = $this->t('Password');
    $form[$wrapper . '_button'] = $form[$wrapper . '_button'] = $this->wrapperButton($wrapper, $label, $is_open);
    $form[$wrapper] = $this->wrapperElement($wrapper, $is_open);
    $form[$wrapper]['new_password'] = [
      '#type' => 'password_confirm',
      '#title' => $this->t('New password'),
      '#description' => $this->t('Password should be at least @length characters', ['@length' => ProfileManager::PROFILE_PASSWORD_LENGTH]),
    ];

    // Submit and other actions.
    $form['actions'] = ['#type' => 'actions'];
    $form['actions']['delete'] = [
      '#markup' => DefaultController::linkMarkup('fsa_signin.delete_account_confirmation', $this->t('Cancel your subscription'), ['button cancel']),
    ];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save your changes'),
    ];
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

      if (!preg_match('/^44[0-9 ]{7,}$/', $phone)) {
        $form_state->setErrorByName('phone', ['#markup' => $this->t('Phone number should be prefixed with international country code, "44" for UK. Special characters are not allowed.<br /><br />Proper phone number format is "447700912345"')]);
      }
      if ($phone == '') {
        $form_state->setErrorByName('phone', $this->t('You selected to receive alerts via SMS, please enter your phone number.'));
      }
    }

    $password = $form_state->getValue('new_password');
    $length = ProfileManager::PROFILE_PASSWORD_LENGTH;
    if ($password != '' && strlen($password) < $length) {
      $form_state->setErrorByName(
        'new_password',
        $this->t('Password not updated: Please use a password of @length or more characters.',
          ['@length' => $length]
        )
      );

    }

  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    /** @var \Drupal\user\Entity\User $account */
    $account = $form_state->getValue('account');

    $food_alerts = DefaultController::storableProfileFieldValue($form_state->getValue('subscribed_food_alerts'));
    $account->set('field_subscribed_food_alerts', $food_alerts);

    $allergy_alerts = $form_state->getValue('subscribed_allergy_alerts');
    // Unset the helper.
    unset($allergy_alerts['all']);
    $account->set('field_subscribed_notifications', $allergy_alerts);

    $subscribed_news = $form_state->getValue('subscribed_news');
    // Unset the helper.
    unset($subscribed_news['all']);
    $account->set('field_subscribed_news', $subscribed_news);

    $subscribed_cons = $form_state->getValue('subscribed_cons');
    // Unset the helper.
    unset($subscribed_cons['all']);
    $account->set('field_subscribed_cons', $subscribed_cons);

    $email = $form_state->getValue('email');
    $account->setEmail($email);

    $delivery_method = $form_state->getValue('delivery_method');
    $delivery_method = array_filter(array_values($delivery_method));
    $account->set('field_delivery_method', $delivery_method);

    $phone = str_replace(' ', '', $form_state->getValue('phone'));
    if (in_array('sms', $delivery_method)) {
      // Only store the phone number if user subscribed via SMS.
      $account->set('field_notification_sms', $phone);
    }
    else {
      $account->set('field_notification_sms', '');
    }

    $email_notification_delivery = $form_state->getValue('email_notification_delivery');
    $account->set('field_notification_method', $email_notification_delivery);

    $language = $form_state->getValue('email_notification_language');
    $account->set('preferred_langcode', $language);

    $account->save();

    drupal_set_message($this->t('Changes saved.'));
  }

  /**
   * Wrapper button.
   *
   * @param string $wrapper
   *   Machine name of the wrapper.
   * @param string $label
   *   Label of the wrapper.
   * @param bool $is_open
   *   Open/close state.
   *
   * @return array
   *   Wrapper button for FAPI.
   */
  private function wrapperButton($wrapper, $label, $is_open) {
    if ($is_open) {
      $class_open = ' is-open';
      $aria_expanded = 'true';
    }
    else {
      $class_open = FALSE;
      $aria_expanded = 'false';
    }
    return [
      '#type' => 'item',
      '#prefix' => '<div class="toggle-button ' . $wrapper . '-button' . $class_open . '" role="button" aria-expanded="' . $aria_expanded . '" data-state="is-open" data-theme="dynamic" data-state-element="#collapsible-' . $wrapper . '"  aria-controls="collapsible-' . $wrapper . '"><div class="toggle-button__item">' . $label . '</div>',
      '#suffix' => '<div class="toggle-button__item toggle-button__item--icon ' . $wrapper . '-button-icon"><div class="toggle-button__fallback-icon"></div></div></div>',
    ];
  }

  /**
   * Wrapper element.
   *
   * @param string $wrapper
   *   Machine name of the wrapper.
   * @param bool $is_open
   *   Open/close state.
   *
   * @return array
   *   Item array for FAPI.
   */
  private function wrapperElement($wrapper, $is_open) {
    if ($is_open) {
      $class_open = ' is-open';
    }
    else {
      $class_open = FALSE;
    }
    return [
      '#type' => 'item',
      '#prefix' => '<div class="toggle-content ' . $wrapper . '-content js-toggle-content' . $class_open . '" id="collapsible-' . $wrapper . '">',
      '#suffix' => '</div>',
      '#attributes' => [
        'class' => [
          'field-wrapper-' . $wrapper,
        ],
      ],
    ];
  }

}
