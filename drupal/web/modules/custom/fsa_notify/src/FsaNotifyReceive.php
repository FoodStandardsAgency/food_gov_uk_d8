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

    // @todo: Store bearer token as configuration.
    $bearer_token = '0kZPJBYd8VxEZ4V3r0APMA';
    $auth_bearer = 'Bearer ' . $bearer_token;

    $content = $request->getContent();

    // Require bearer token set in Notify settings.
    if ($request->headers->get('authorization') != $auth_bearer) {
      return new JsonResponse([
        'status' => 'error',
        'message' => $this->t('Incorrect token.'),
      ]);
    }

    // Error for empty content.
    if (empty($content)) {
      return new JsonResponse([
        'status' => 'error',
        'message' => $this->t('No content.'),
      ]);
    }

    $params = Json::decode($content);

    if (!empty($params)) {
      // Log the succesful event with message values.
      \Drupal::logger('fsa_notify')->info(
        $this->t('Received Notify SMS from @source_number.<br />Callback message:<pre>@params</pre>',
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
      // Log failure.
      return new JsonResponse([
        'status' => 'error',
        'message' => $this->t('error'),
      ]);
    }

  }

}
