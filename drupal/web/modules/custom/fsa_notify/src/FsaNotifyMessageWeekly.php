<?php

namespace Drupal\fsa_notify;

use Drupal\fsa_notify\FsaNotifyMessage;

class FsaNotifyMessageWeekly extends FsaNotifyMessage {

  private $subject;

  public function __construct() {
    parent::__construct();
    $this->subject = t('Weekly digest');
  }

  protected function assemble($items) {

    $items = implode("\n", $items);

    $items = [[
      'subject' => $this->subject,
      'date' => $this->date,
      'allergy_alerts' => $items,
      'login' => $this->login_url,
      'unsubscribe' => $this->unsubscribe_url,
    ]];

    return $items;
  }

  protected function theme($item) {
    $title = $item->getTitle();
    $created = $item->getCreatedTime();
    $created = \Drupal::service('date.formatter')->format($created, 'medium');
    $line1 = sprintf('%s: %s', $created, $title);

    $link = $this->url($item);
    $more = t('Read more');
    $line2 = sprintf('%s: %s', $more, $link);

    $item = "$line1\n$line2\n";
    return $item;
  }

}
