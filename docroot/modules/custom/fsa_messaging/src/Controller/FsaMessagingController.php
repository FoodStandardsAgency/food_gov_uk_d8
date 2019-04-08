<?php

namespace Drupal\fsa_messaging\Controller;

use Drupal\Core\Controller\ControllerBase;
use \Symfony\Component\HttpFoundation\Response;

/**
 * Provides a controller for the block content.
 */
class FsaMessagingController extends ControllerBase {

  /**
   * Render the message block if it should be shown.
   */
  public function getContent() {
    $response = new Response('');
    $config = \Drupal::config('fsa_messaging.settings');

    if ($config->get('fsa_messaging_active')) {
      $message = $config->get('fsa_messaging_message');
      $markup = check_markup(
        $message['value'],
        $message['format']
      );
      $style = $config->get('fsa_messaging_style');

      $block = [
        '#theme' => 'fsa_messaging_block',
        '#message' => $markup,
        '#cache' => [
          'keys' => ['block', 'fsa_messaging'],
          'contexts' => ['languages'],
          'tags' => ['block:fsa_messaging'],
          'max-age' => 600,
        ],
      ];
      if ($style != 'default') {
        $block['#style'] = $style;
      }
      $response->setContent(drupal_render($block));
    }

    return $response;
  }
}
