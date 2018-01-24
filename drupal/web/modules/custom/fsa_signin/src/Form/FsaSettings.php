<?php

namespace Drupal\fsa_signin\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * FSA signin settings page.
 */
class FsaSettings extends FormBase {

  private $redirectSignin = 'fsa_signin.redirect';

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'fsa_signin.settings';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $weight = 0;

    $redirect_signin = \Drupal::state()->get($this->redirectSignin);
    $redirect_signin = (bool) $redirect_signin;

    $form['redirect_signin_pages'] = [
      '#type' => 'checkbox',
      '#title' => t('Redirect anonymous signin and account page links to old service'),
      '#default_value' => $redirect_signin,
      '#weight' => $weight++,
    ];

    $keys = [
      'fsa_signin.profile_manage_url' => t('Profile manage external URL'),
      'fsa_signin.signup_url' => t('Profile signup external URL'),
    ];

    foreach ($keys as $key => $title) {
      // Cannot have dot in render array key.
      $key2 = str_replace('.', '___', $key);
      $value = \Drupal::state()->get($key);
      $form[$key2] = [
        '#type' => 'url',
        '#title' => $title,
        '#default_value' => $value,
        '#states' => [
          'visible' => [
            ':input[name="redirect_signin_pages"]' => ['checked' => TRUE],
          ],
        ],
        '#weight' => $weight++,
      ];
    }

    $form['actions'] = [
      '#type' => 'actions',
      '#weight' => $weight++,
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save'),
      '#button_type' => 'primary',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $keys = [
      'fsa_signin.profile_manage_url',
      'fsa_signin.signup_url',
    ];

    foreach ($keys as $key) {
      // Cannot have dot in render array key.
      $key2 = str_replace('.', '___', $key);
      $value = $form_state->getValue($key2);
      \Drupal::state()->set($key, $value);
    }

    if (empty($form_state->getValue('redirect_signin_pages'))) {
      \Drupal::state()->delete($this->redirectSignin);
      drupal_set_message(t('Account signin allowed on this site.'));
    }
    else {
      \Drupal::state()->set($this->redirectSignin, 1);
      drupal_set_message(t('Account pages redirected to old service.'));
    }

    // Let the admin know something happened.
    drupal_set_message($this->t('Signin settings updated.'));

  }

}
