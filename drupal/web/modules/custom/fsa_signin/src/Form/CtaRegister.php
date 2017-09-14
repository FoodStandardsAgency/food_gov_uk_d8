<?php

namespace Drupal\fsa_signin\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class CtaRegister.
 */
class CtaRegister extends FormBase {


  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'cta_register';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['title'] = [
      '#markup' => '<h3>' . $this->t("New to food.gov.uk?") . '</h3>',
    ];
    $form['description'] = [
      '#markup' => $this->t("By registering to food.gov.uk you can stay up-to-date with FSA news stories and alerts by subscribing to our email and SMS updates."),
    ];
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Register'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    /*
    foreach ($form_state->getValues() as $key => $value) {
      drupal_set_message($key . ': ' . $value);
    }
    */
    $form_state->setRedirect('user.register');
  }

}
