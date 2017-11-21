<?php

namespace Drupal\fsa_notify;

use Drupal\fsa_notify\FsaNotifyMessage;

class FsaNotifyMessageImmediate extends FsaNotifyMessage {

  private $subject;

  public function __construct() {
    parent::__construct();
    $this->subject = t('Immediate');
  }

  protected function assemble($items) {

    foreach ($items as &$item) {
      $item = [
        'subject' => $this->subject,
        'date' => $this->date,
        'allergy_alerts' => $item,
        'login' => $this->login_url,
        'unsubscribe' => $this->unsubscribe_url,
      ];
    }

    return $items;
  }

  protected function theme($item) {
    $title = $item->getTitle();
    $line1 = sprintf('%s', $title);

    $link = $this->url($item);
    $more = t('Read more');
    $line2 = sprintf('%s: %s', $more, $link);

    $item = "$line1\n$line2\n";
    return $item;
  }

}
