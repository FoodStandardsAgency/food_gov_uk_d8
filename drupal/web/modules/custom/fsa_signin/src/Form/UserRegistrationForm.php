<?php

namespace Drupal\fsa_signin\Form;

use Drupal\fsa_signin\Controller\DefaultController;
use Drupal\user\Entity\User;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;

/**
 * Class EmailSubscriptionsForm.
 */
class UserRegistrationForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'user_registration_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    /** @var \Drupal\user\PrivateTempStore $tempstore */
    $tempstore = \Drupal::service('user.private_tempstore')->get('fsa_signin');
    $food_alerts = $tempstore->get('food_alert_registration');
    $alert_tids = $tempstore->get('alert_tids_for_registration');
    $news_registration = $tempstore->get('news_tids_for_registration');

    // Only add possibility to submit if user has selected something to
    // subscribe to.
    if (
      $tempstore->get('alert_tids_for_registration') != NULL ||
      $tempstore->get('food_alert_registration') != NULL ||
      $tempstore->get('news_tids_for_registration') != NULL) {

    }
    else {
      drupal_set_message($this->t('Please subscribe to at least one category on previous pages.'));
      $form['actions']['back'] = [
        '#markup' => DefaultController::linkMarkup('fsa_signin.user_preregistration_news_form', $this->t('Previous'), ['back arrow']),
      ];
      return $form;
    }

    $form['subscribed_food_alerts'] = [
      '#type' => 'value',
      '#value' => $food_alerts,
    ];
    $form['subscribed_notifications'] = [
      '#type' => 'value',
      '#value' => $alert_tids,
    ];
    $form['subscribed_news'] = [
      '#type' => 'value',
      '#value' => $news_registration,
    ];
    $form['description'] = [
      '#markup' => '<h2>' . $this->t('Delivery options') . '</h2><p>' . $this->t('How do you want to receive information from us?') . '</p>',
    ];

    $form['alert_container'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['alert-preferences']],
    ];
    $form['alert_container']['delivery_method'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('I want to receive food and allergy alerts via'),
      '#options' => [
        'email' => $this->t('Email'),
        'sms' => $this->t('SMS'),
      ],
    ];
    $form['alert_container']['news_delivery_method'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('I want to receive news and consultations via'),
      '#description' => $this->t('News are available only via email.'),
      '#options' => [
        'email' => $this->t('Email'),
      ],
    ];
    $form['alert_container']['sms_notification_delivery'] = [
      '#type' => 'checkboxes',
      '#options' => [],
      '#title' => $this->t('SMS frequency'),
      '#description' => $this->t('SMS updates are sent immediately'),
    ];
    $form['alert_container']['delivery_frequency_email'] = [
      '#type' => 'radios',
      '#title' => $this->t('Email frequency'),
      '#options' => [
        'immediate' => $this->t('Send updates immediately'),
        'daily' => $this->t('Send updates daily'),
        'weekly' => $this->t('Send updated weekly'),
      ],
      '#default_value' => 'immediate',
    ];
    $form['personal_container'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['personal-info']],
    ];
    $form['personal_container']['title'] = [
      '#markup' => '<h3>' . $this->t('Personal information') . '</h3>',
    ];
    $form['personal_container']['email'] = [
      '#type' => 'email',
      '#title' => $this->t('Email address'),
    ];
    $form['personal_container']['phone'] = [
      '#type' => 'tel',
      '#title' => $this->t('Phone number'),
    ];

    $form['language_container'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['language-info']],
    ];
    $form['language_container']['title'] = [
      '#markup' => '<h3>' . $this->t('Choose language') . '</h3>',
    ];
    $form['language_container']['language'] = [
      '#type' => 'radios',
      '#options' => [
        'en' => $this->t('English'),
        'cy' => $this->t('Cymraeg'),
      ],
      '#default_value' => \Drupal::currentUser()->getPreferredLangcode(),
    ];

    $form['actions'] = ['#type' => 'actions'];
    $form['actions']['back'] = [
      '#markup' => DefaultController::linkMarkup('fsa_signin.user_preregistration_news_form', $this->t('Previous'), ['back arrow']),
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    ];

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

    $user = User::create();
    $email = $form_state->getValue('email');
    $language = $form_state->getValue('language');
    $email_frequency = $form_state->getValue('delivery_frequency_email');
    $subscribed_food_alerts = DefaultController::storableProfileFieldValue($form_state->getValue('subscribed_food_alerts'));
    $subscribed_notifications = $form_state->getValue('subscribed_notifications');
    $subscribed_news = $form_state->getValue('subscribed_news');

    // Mandatory settings.
    $user->setPassword(user_password());
    $user->enforceIsNew();
    $user->setEmail($email);
    $user->setUsername($email);

    // Optional settings.
    $user->set('init', $email);
    $user->set('langcode', $language);
    $user->set('preferred_langcode', $language);
    $user->activate();

    // Set the field values.
    $user->set('field_subscribed_food_alerts', $subscribed_food_alerts);
    $user->set('field_subscribed_notifications', $subscribed_notifications);
    $user->set('field_subscribed_news', $subscribed_news);
    $user->set('field_notification_method', $email_frequency);

    try {
      // Save user account.
      $result = $user->save();
      user_login_finalize($user);
    }
    catch (\Exception $e) {
      drupal_set_message($this->t('An error occurred while creating an account.'), 'error');
    }
    $form_state->setRedirect('fsa_signin.user_registration_thank_you');
  }

}
