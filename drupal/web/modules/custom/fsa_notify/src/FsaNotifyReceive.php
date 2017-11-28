<?php

namespace Drupal\fsa_notify;

use Drupal\fsa_signin\SignInService;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Controller\ControllerBase;
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
   * @var \Drupal\fsa_signin\SignInService
   */
  protected $signInService;


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
   * @param \Drupal\fsa_signin\SignInService $signInService
   */
  public function __construct(RequestStack $request, SignInService $signInService) {
    $this->request = $request;
    $this->signInService = $signInService;
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
      $container->get('request_stack'),
      $container->get('fsa_signin.service')
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

      $msg = '';
      $sms_message = $params['message'];

      if (preg_match("#^STOP(.*)$#i", $sms_message)) {
        // When a "STOP" command is issued.
        // Strip the command part for values and send for the unsubscriber.
        $values = preg_replace('/^STOP\s*/i', '', $sms_message);
        $unsubscribed = $this->signInService->unsubscribeFromAlerts($params['source_number'], $values);
        $msg = $unsubscribed['message'];
        $action = $msg;
      }
      else {
        $action = $this->t('sent SMS with no action detected');
      }

      \Drupal::logger('fsa_notify')->info(
        $this->t('Notify SMS callback: @action<br />Message body:<pre>@params</pre>',
          [
            '@action' => $action,
            '@params' => var_export($params, TRUE),
          ]
        )
      );

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
          $this->t('Notify SMS callback: @msg', ['@msg' => $error_msg])
        );
      }
      return new JsonResponse([
        'status' => 'error',
        'message' => $error_msg,
      ]);
    }

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
