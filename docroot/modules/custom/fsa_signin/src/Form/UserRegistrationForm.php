<?php

namespace Drupal\fsa_signin\Form;

use Drupal\fsa_custom\FsaCustomHelper;
use Drupal\fsa_signin\Controller\DefaultController;
use Drupal\user\Entity\User;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

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

    if (\Drupal::state()->get('fsa_signin_subscriptions_offline') === TRUE) {
      $form['subscribe_description_1'] = [
        '#markup' => '<p>' . $this->t("We’re experiencing technical issues with this service. We’re working to resolve these as soon as possible. Please check again later.") . '</p>',
      ];
      return $form;
    }
    $form['#attributes']['class'][] = DefaultController::PROFILE_FORM_HTML_CLASS;

    $form['subscribe_description_1'] = [
      '#markup' => '<p>' . $this->t("Create an account to get food and allergy alerts by email or text message. This is a free service.") . '</p>',
    ];
    $form['subscribe_description_2'] = [
      '#markup' => '<p><small>' . $this->t('Before you can subscribe we need to verify your email address. Please enter a valid email address in the box below and click "Create account".') . '</small></p>',
    ];
    $form['email'] = [
      '#type' => 'email',
      '#title' => $this->t('Email address'),
      '#required' => TRUE,
    ];

    $form['language_container'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Choose language'),
      '#attributes' => ['class' => ['language-info']],
    ];
    $form['language_container']['language'] = [
      '#type' => 'radios',
      '#options' => [
        'en' => $this->t('English'),
        'cy' => $this->t('Cymraeg'),
      ],
      '#default_value' => \Drupal::languageManager()->getCurrentLanguage()->getId(),
    ];

    $form['links']['privacy_notice'] = [
      '#type' => 'fieldset',
    ];
    $form['links']['privacy_notice']['link'] = [
      '#markup' => FsaCustomHelper::privacyNoticeLink('alerts'),
    ];

    $form['links']['privacy_notice']['privacy_notice'] = [
      '#type' => 'checkboxes',
      '#options' => [
        'yes' => $this->t('I accept the terms of this privacy statement'),
      ],
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Create account'),
    ];
    $form['subscribe_beta_description'] = [
      '#prefix' => '<br /><br /><small>',
      '#suffix' => '</small><br />',
      '#markup' => DefaultController::betaSigninDescription('long'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $email = $form_state->getValue('email');
    if (user_load_by_mail($email)) {
      $form_state->setErrorByName('email', $this->t('Account with @email already exist. Please <a href="/user">log in</a>.', ['@email' => $email]));
    }

    $privacy_notice = $form_state->getValue('privacy_notice');
    if (empty(array_filter(array_values($privacy_notice)))) {
      $form_state->setErrorByName('privacy_notice', $this->t('Please accept the privacy statement'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $user = User::create();
    $email = $form_state->getValue('email');
    $language = $form_state->getValue('language');

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

    try {
      // Save user account.
      $user->save();

      // Save the new UID to the session to use on the data layer.
      // See thankYouPage() in DefaultController.php.
      $session = \Drupal::service('user.private_tempstore')->get('fsa_signin');
      $session->set('regUid', $user->get('uid')->value);

      _user_mail_notify('register_no_approval_required', $user);
    }
    catch (\Exception $e) {
      drupal_set_message($this->t('An error occurred while creating an account.'), 'error');
    }
    $form_state->setRedirect('fsa_signin.user_registration_thank_you');
  }

}
