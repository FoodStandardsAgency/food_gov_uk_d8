<?php

namespace Drupal\fsa_notify;

use Drupal\fsa_notify\FsaNotifyMessage;

class FsaNotifyMessageSms extends FsaNotifyMessage {

  protected function assemble($items) {
    $items = array_map(function ($item) {
      return ['message' => $item];
    }, $items);
    return $items;
  }

  protected function theme($item) {
    $url = $this->url($item);
    return $url;
  }

}
