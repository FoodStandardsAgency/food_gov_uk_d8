<?php

namespace Drupal\fsa_notify;

/**
 * FSA Notify SMS sending class.
 */
class FsaNotifyMessageSms extends FsaNotifyMessage {

  /**
   * Todo: document.
   */
  protected function assemble($items) {
    $items = array_map(function ($item) {
      return ['message' => $item];
    }, $items);
    return $items;
  }

  /**
   * Todo: document.
   */
  protected function theme($item) {
    $url = $this->url($item);
    return $url;
  }

}
