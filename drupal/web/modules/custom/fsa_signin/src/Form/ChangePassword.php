<?php

namespace Drupal\fsa_signin\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\fsa_signin\Controller\DefaultController;
use Drupal\user\Entity\User;
use Drupal\fsa_signin\SignInService;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class ChangePassword.
 */
class ChangePassword extends FormBase {

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
    return 'change_password';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['password']['new_password'] = [
      '#type' => 'password_confirm',
      '#description' => $this->t('Password should be at least @length characters', ['@length' => ChangePassword::PROFILE_PASSWORD_LENGTH]),
    ];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Change password'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {

    $password = $form_state->getValue('new_password');
    $length = ChangePassword::PROFILE_PASSWORD_LENGTH;
    if (strlen($password) < $length) {
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
    $account = User::load(\Drupal::currentUser()->id());

    $password = $form_state->getValue('new_password');
    if ($password != '') {
      $account->setPassword($password);
    }

    if ($account->save()) {
      drupal_set_message($this->t('Your password was successfully changed.'));
      $form_state->setRedirect('fsa_signin.user_preregistration_alerts_form');
    }
    else {
      drupal_set_message($this->t('An error occurred saving your password. Please try again.'), 'error');
    }
  }

}
