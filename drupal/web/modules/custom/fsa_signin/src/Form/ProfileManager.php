<?php

namespace Drupal\fsa_signin\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\user\Entity\User;

/**
 * Class EmailPreferencesForm.
 */
class ProfileManager extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'email_preferences_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, User $account = NULL) {

    // To control the togglable wrappers oepn/close state.
    $is_open = FALSE;

    $form['account'] = [
      '#type' => 'value',
      '#value' => $account,
    ];

    // Food and allergy alerts wrapper.
    $wrapper = 'food-allergy';
    $label = $this->t('Food and allergy alerts');
    $form[$wrapper . '_button'] = $this->wrapperButton($wrapper, $label, $is_open);
    $form[$wrapper] = $this->wrapperElement($wrapper, $is_open);

    // News and consultations wrapper.
    $wrapper = 'news-consultation';
    $label = $this->t('News and consultations');
    $form[$wrapper . '_button'] = $this->wrapperButton($wrapper, $label, $is_open);
    $form[$wrapper] = $this->wrapperElement($wrapper, $is_open);

    // Delivery options wrapper.
    $wrapper = 'delivery';
    $label = $this->t('Delivery options');
    $form[$wrapper . '_button'] = $this->wrapperButton($wrapper, $label, $is_open);
    $form[$wrapper] = $this->wrapperElement($wrapper, $is_open);
    $form[$wrapper]['email'] = [
      '#type' => 'email',
      '#default_value' => $account->getEmail(),
    ];
    $form[$wrapper]['email_notification_delivery'] = [
      '#type' => 'radios',
      '#title' => $this->t('Email delivery preference'),
      '#options' => [
        'immediate' => $this->t('Send updates immediately by email'),
        'daily' => $this->t('Send updates daily by email'),
        'weekly' => $this->t('Send updates weekly by email'),
      ],
      '#default_value' => $account->get('field_notification_method')->getString(),
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


    // Password wrapper.
    $wrapper = 'password';
    $label = $this->t('Password');
    $form[$wrapper . '_button'] = $form[$wrapper . '_button'] = $this->wrapperButton($wrapper, $label, $is_open);
    $form[$wrapper] = $this->wrapperElement($wrapper, $is_open);
    $form[$wrapper]['new_password'] = [
      '#type' => 'password_confirm',
      '#title' => $this->t('New password'),
    ];

    // Submit and other actions.
    $form['actions'] = ['#type' => 'actions'];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save your changes'),
    ];
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
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    /** @var \Drupal\user\Entity\User $account */
    $account = $form_state->getValue('account');
    $email = $form_state->getValue('email');
    $email_notification_delivery = $form_state->getValue('email_notification_delivery');
    $email_notification_language = $form_state->getValue('email_notification_language');
    $account->setEmail($email);
    $account->set('field_notification_method', $email_notification_delivery);
    $account->set('preferred_langcode', $email_notification_language);
    $account->save();
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
      '#prefix' => '<div class="toggle-button js-toggle-button ' . $wrapper . '-button' . $class_open . '" role="button" aria-expanded="' . $aria_expanded . '" aria-controls="collapsible-' . $wrapper . '"><div class="toggle-button__item">' . $label . '</div>',
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
