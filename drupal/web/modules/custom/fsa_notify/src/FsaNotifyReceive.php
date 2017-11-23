<?php

namespace Drupal\fsa_notify;

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
    $bearer_token_notify = \Drupal::state()->get('fsa_notify.bearer_token');

    $bearer_token_request = $this->getBearerToken($request);

    if ($bearer_token_request != $bearer_token_notify) {
      if ($bearer_token_request === NULL) {
        $error_msg = $this->t('No access token in Authentication header.');
      }
      else {
        $error_msg = $this->t('Request with invalid access token');
      }


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
      \Drupal::logger('fsa_notify')->info(
        $this->t('Notify callback: Received SMS from @source_number.<br />Message body:<pre>@params</pre>',
          [
            '@source_number' => $params['source_number'],
            '@params' => var_export($params, TRUE),
          ]
        )
      );

      return new JsonResponse([
        'status' => 'ok',
        'message' => $this->t('Received SMS'),
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
   * Get Bearer access token.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The Drupal request.
   *
   * @return string|null
   *   The Bearer token or null.
   */
  public function getBearerToken(Request $request) {

    // Get the Authorization header.
    $auth_header = $request->headers->get('Authorization');

    if (!empty($auth_header)) {
      if (preg_match('/Bearer\s(\S+)/', $auth_header, $matches)) {
        return $matches[1];
      }
    }
    return NULL;
  }

}
