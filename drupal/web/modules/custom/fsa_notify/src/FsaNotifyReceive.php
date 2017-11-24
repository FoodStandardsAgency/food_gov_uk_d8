<?php

namespace Drupal\fsa_notify;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Controller\ControllerBase;
use Drupal\user\Entity\User;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * FsaNotifyReceive controller class.
 *
 * @package Drupal\fsa_notify\Controller
 */
class FsaNotifyReceive extends ControllerBase {

  /**
   * Request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  public $request;

  /**
   * FsaNotifyReceive constructor.
   *
   * @param \Symfony\Component\HttpFoundation\RequestStack $request
   *   The request.
   */
  public function __construct(RequestStack $request) {
    $this->request = $request;
  }

  /**
   * Create DI.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   Container.
   *
   * @return static
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('request_stack')
    );
  }

  /**
   * The sms callback.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   Json response.
   */
  public function sms(Request $request) {

    // For logging all errors.
    $logger = \Drupal::state()->get('fsa_notify.log_callback_errors');

    // Store request content to a var.
    $content = $request->getContent();

    if (!$this->notifyAuthentication($request)) {
      $error_msg = $this->t('Authentication failure.');

      if ($logger) {
        \Drupal::logger('fsa_notify')->warning(
          $this->t('Notify callback: @msg', ['@msg' => $error_msg])
        );
      }

      return new JsonResponse([
        'status' => 'error',
        'message' => $error_msg,
      ]);
    }

    if (empty($content)) {
      $error_msg = $this->t('Request with empty content');
      if ($logger) {
        \Drupal::logger('fsa_notify')->warning(
          $this->t('Notify callback: @msg', ['@msg' => $error_msg])
        );
      }

      return new JsonResponse([
        'status' => 'error',
        'message' => $error_msg,
      ]);
    }

    $params = Json::decode($content);

    if (!empty($params)) {
      // Log the succesful event with message values.
      // @todo: log the unsubscribe.
      \Drupal::logger('fsa_notify')->info(
        $this->t('Notify callback: Received SMS from @source_number.<br />Message body:<pre>@params</pre>',
          [
            '@source_number' => $params['source_number'],
            '@params' => var_export($params, TRUE),
          ]
        )
      );

      $msg = '';
      $sms_message = $params['message'];
      $stop = preg_match("#^STOP(.*)$#i", $sms_message);
      if ($stop) {

        $values = ltrim($sms_message, 'STOP ');
        $msg = $this->unsubscribeFromAlerts($params['source_number'], $values);
      }

      return new JsonResponse([
        'status' => 'ok',
        'message' => $msg,
        'uuid' => $params['id'],
      ]);
    }
    else {
      $error_msg = $this->t('No parameters in message body');

      if ($logger) {
        \Drupal::logger('fsa_notify')->warning(
          $this->t('Notify callback: @msg', ['@msg' => $error_msg])
        );
      }
      return new JsonResponse([
        'status' => 'error',
        'message' => $error_msg,
      ]);
    }

  }

  /**
   * Unsubscribe user from alerts.
   *
   * @param string $phone
   *   Phone number to unsibscribe.
   * @param string $values
   *   That to unsubscribe from.
   *
   * @return string
   *   .
   */
  public function unsubscribeFromAlerts($phone, $values) {
    $message = '';

    // Match the phone number with stored format.
    // @todo: consider all cases of different formats.
    $phone = '+' . $phone;

    $values = explode(' ', $values);

    // Get what to unsubscribe from.
    if (count($values) >= 1) {
      // If user passed id's only.
      $unsubscribe = 'ids';
    }
    else {
      foreach ($values as $value) {
        if ($value == 'all') {
          $unsubscribe = $value;
          break;
        }
      }
    }

    // Get user(s) with phone number from the callback.
    $query = \Drupal::entityQuery('user');
    $query->condition('uid', 0, '>');
    $query->condition('status', 1);
    $query->condition('field_notification_sms', $phone, '=');
    $uids = $query->execute();

    // Could match multiple users since phone number is not unique field.
    foreach ($uids as $uid) {
      $user = User::load($uid);

      // Unsubscribe from all notifications.
      switch ($unsubscribe) {
        case 'all':
          $user->field_subscribed_notifications->setValue([]);
          $user->save();
          $message = 'Unsubscribed ' . $uid . ' from all alerts';
          break;

        case 'ids':
          $tids = '';
          foreach ($values as $tid) {
            $tids .= $tid;
            if (!is_numeric($tid)) {
              // @todo: Get term by name.
            }

            // @todo: unsubscribe from defined alerts.

          }

          $message = 'Unsubscribed user ' . $uid . ' from ' . $tids . ' alerts';
          break;

      }
    }

    return $message;

  }

  /**
   * Check for Notify authentication.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The Drupal request.
   *
   * @return bool
   *   TRUE or FALSE for authentiaction.
   */
  public function notifyAuthentication(Request $request) {
    // Get the Authorization header from request.
    $auth_header = $request->headers->get('Authorization');

    if (!empty($auth_header)) {

      // Cannot use 2 auth mechanisms, check for either Bearer token OR httpauth
      // required in Wunder environments.
      if (preg_match('/Bearer\s(\S+)/', $auth_header, $matches)) {
        // Notify Bearer token matching.
        $bearer_token = \Drupal::state()->get('fsa_notify.bearer_token');
        if ($bearer_token == $matches[1]) {
          return TRUE;
        }

      }
      elseif (preg_match('/Basic\s(\S+)/', $auth_header, $matches)) {
        // Wunder environment httpauth matching.
        if (base64_encode('wunder:tools') == $matches[1]) {
          return TRUE;
        }
      }
    }

    return FALSE;
  }

}
