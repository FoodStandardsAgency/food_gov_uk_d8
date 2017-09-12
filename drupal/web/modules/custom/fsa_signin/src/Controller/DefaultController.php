<?php

namespace Drupal\fsa_signin\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Class DefaultController.
 */
class DefaultController extends ControllerBase {

  public function signInPage() {
    $title = ['#markup' => '<h1>' . $this->t('Sign in') . '</h1>'];
    $login_form = \Drupal::formBuilder()->getForm(\Drupal\user\Form\UserLoginForm::class);
    $cta_register_form = \Drupal::formBuilder()->getForm(\Drupal\fsa_signin\Form\CtaRegister::class);
    $send_pwd_form = \Drupal::formBuilder()->getForm(\Drupal\fsa_signin\Form\SendPasswordEmailForm::class);

    return [
      $title,
      $login_form,
      $cta_register_form,
      $send_pwd_form
    ];
  }

  public function registerPage() {
    $entity = \Drupal::entityTypeManager()->getStorage('user')->create(array());
    $formObject = \Drupal::entityTypeManager()
      ->getFormObject('user', 'register')
      ->setEntity($entity);
    $register_form = \Drupal::formBuilder()->getForm($formObject);

    return $register_form;
  }

}
