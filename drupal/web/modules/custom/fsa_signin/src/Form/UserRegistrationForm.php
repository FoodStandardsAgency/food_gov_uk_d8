<?php

namespace Drupal\fsa_signin\Form;

use Drupal\Component\Utility\Random;
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
    $alert_tids = $tempstore->get('alert_tids_for_registration');

    $form['subscribed_notifications'] = [
      '#type' => 'value',
      '#value' => $alert_tids,
    ];
    $form['description'] = [
      '#markup' => '<h2>' . $this->t('Type and frequency') . '</h2><p>' . $this->t('By how and how often you want to receive information from us?') . '</p>',
    ];

    $form['alert_container'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['alert-preferences']],
    ];
    $form['alert_container']['title'] = [
      '#markup' => '<h3>' . $this->t('I want to receive food and allergy alerts via') . '</h3>',
    ];
    $form['alert_container']['delivery_method'] = [
      '#type' => 'radios',
      '#options' => [
        'email' => $this->t('Email'),
        'sms' => $this->t('SMS'),
      ],
    ];
    $form['alert_container']['delivery_frequency_email'] = [
      '#type' => 'radios',
      '#title' => $this->t('Frequency'),
      '#options' => [
        'immediate' => $this->t('Send updates immediately'),
        'daily' => $this->t('Send updates daily'),
        'weekly' => $this->t('Send updated weekly'),
      ],
      '#states' => [
        // Only display when user selected 'email' as the delivery method.
        'visible' => [
          ':input[name="delivery_method"]' => ['value' => 'email'],
        ],
      ],
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
      '#title' => $this->t('Email'),
    ];
    $form['personal_container']['phone'] = [
      '#type' => 'tel',
      '#title' => $this->t('Phone'),
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

    $form['alert_tids'] = [
      '#type' => 'value',
      '#value' => $alert_tids,
    ];
    $form['actions'] = array('#type' => 'actions');
    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    );
    $form['actions']['back'] = [
      '#markup' => Link::createFromRoute($this->t('Previous'),'fsa_signin.default_controller_emailSubscriptionsPage', [], ['attributes' => ['class' => 'button black left']])->toString(),
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
    $alert_tids = $form_state->getValue('alert_tids');

    $user = \Drupal\user\Entity\User::create();
    $email = $form_state->getValue('email');
    $language = $form_state->getValue('language');
    $email_frequency = $form_state->getValue('delivery_frequency_email');
    $subscribed_notifications = $form_state->getValue('subscribed_notifications');

    // Mandatory settings
    $user->setPassword(user_password());
    $user->enforceIsNew();
    $user->setEmail($email);
    $user->setUsername($email);

    // Optional settings
    $user->set('init', $email);
    $user->set('langcode', $language);
    $user->set('preferred_langcode', $language);
    $user->activate();

    // Field values
    $user->set('field_subscribed_notifications', $subscribed_notifications);
    $user->set('field_notification_method', $email_frequency);

    try {
      // Save user account.
      $result = $user->save();
      user_login_finalize($user);

      //drupal_set_message($this->t('Thank you! Your selections has been saved.'));
    }
    catch (\Exception $e) {
      drupal_set_message($this->t('An error occurred while creating an account.'), 'error');
    }
    $form_state->setRedirect('fsa_signin.user_registration_thank_you');
  }

}
