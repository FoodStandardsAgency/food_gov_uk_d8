<?php

namespace Drupal\fsa_signin\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\fsa_signin\Event\UserUnsubscribeEvent;
use Drupal\fsa_signin\SignInService;
use Drupal\user\Entity\User;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class UnsubscribeForm.
 */
class UnsubscribeForm extends FormBase {

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
    return 'unsubscribe_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $query = \Drupal::request()->query->all();

    // Format of link to unsubscribe: ?email=foo@bar.com&id=123
    // where uid must match with respective email in db.
    $email = (isset($query['email'])) ? $query['email'] : FALSE;
    $uid = (isset($query['id'])) ? $query['id'] : FALSE;

    $user = user_load_by_mail($email);

    if ($user && is_numeric($uid) && \Drupal::service('email.validator')->isValid($email)) {

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
        // When UID does not match with email.
        if (\Drupal::currentUser()->isAnonymous()) {
          $message = $this->t('Cannot unsubscribe. You may need <a href="@url">to log in</a>.', ['@url' => Url::fromRoute('fsa_signin.default_controller_signInPage')->toString()]);
        }
        else {
          $message = $this->t('Cannot unsubscribe. Visit your <a href="@url">profile page</a> to unsubscribe.', ['@url' => Url::fromRoute('fsa_signin.default_controller_deliveryOptionsPage')->toString()]);
        }
        drupal_set_message($message);
      }

    }
    else {
      drupal_set_message($this->t('Cannot unsubscribe: User does not exist or invalid parameters.'));
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

    $email = $form_state->getValue('email');
    $t_options = ['@email' => $email];

    // Emit an event to track this activity.
    $user = user_load_by_mail($email);
    \Drupal::service('event_dispatcher')->dispatch('fsa_alerts_monitor.user.unsubscribe', new UserUnsubscribeEvent($user));

    // For now just unsubscribe from all.
    // @todo: unsubscribeFromAlerts needs to allow unsubscribing specific terms.
    $values = 'all';

    $unsubscribed = $this->signInService->unsubscribeFromAlerts($email, $values);

    if ($unsubscribed['success']) {
      drupal_set_message($unsubscribed['message']);
      $config = \Drupal::config('system.site');

      // Let user know (s)he just unsubscribed.
      $mailManager = \Drupal::service('plugin.manager.mail');

      // @todo: Format nice message for the user?
      $params['message'] = $this->t('You have been unsubscribed from all alerts on FSA website.');
      $params['message'] .= "\r\n\r\n--\r\n";
      $params['message'] .= $config->get('name');

      $langcode = \Drupal::currentUser()->getPreferredLangcode();
      $send = TRUE;
      $result = $mailManager->mail(
        'fsa_signin',
        'unsubscribed',
        $email,
        $langcode,
        $params,
        NULL,
        $send
      );

      if ($result['result'] !== TRUE) {
        drupal_set_message($this->t('There was a problem sending you a confirmation email to @email.', $t_options), 'error');
      }
      else {
        drupal_set_message($this->t('We have sent you an email confirmation to @email.', $t_options));
      }
    }
    else {
      drupal_set_message($this->t('There was a problem unsubscribing @email from alerts.', $t_options), 'error');
    }

    $form_state->setRedirect('<front>');
  }

}
