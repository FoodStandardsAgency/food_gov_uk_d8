<?php

namespace Drupal\fsa_signin\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Class UnsubscribeForm.
 */
class UnsubscribeForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'unsubscribe_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $query = \Drupal::request()->query->all();

    // Format of link to unsubscribe: ?email=foo@bar.com&id=123
    // where uid must match with respective email in db.
    $email = $query['email'];
    $uid = $query['id'];

    if (is_numeric($uid) && \Drupal::service('email.validator')->isValid($email)) {

      $user = user_load_by_mail($email);

      // Check uid matches with respective email.
      if ($user->id() === $uid) {
        $form['description'] = [
          '#markup' => '<p>' . $this->t('Are you sure you want to unsubscribe @email from all alerts', ['@email' => $email]) . '</p>',
        ];

        $form['uid'] = [
          '#type' => 'hidden',
          '#default_value' => $uid,
        ];

        $form['email'] = [
          '#type' => 'hidden',
          '#default_value' => $email,
        ];

        $form['submit'] = [
          '#type' => 'submit',
          '#value' => $this->t('Unsubscribe'),
        ];
      }
      else {
        $signin_url = Url::fromRoute('fsa_signin.default_controller_signInPage')->toString();

        ksm($signin_url);

        $form['description'] = [
          '#markup' => '<p>' . $this->t('Unsubscribing unavailable. You may need <a href="@signin_url">to log in</a>.', ['@signin_url' => $signin_url]) . '</p>',
        ];
      }

    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {

    // This is already done in fodm build() but add "extra layer of security"
    // with validation in case hidden field values are tampered.
    $uid = $form_state->getValue('uid');
    $email = $form_state->getValue('email');
    $user = user_load_by_mail($email);

    if ($user->id() !== $uid) {
      $form_state->setErrorByName(FALSE, $this->t('Unsubscribing failed'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    // @todo: Unsubscribe the user.
    drupal_set_message('Submitted! [unsubscribe to be implemented]');
///    $form_state->setRedirect('<front>');
  }

}
