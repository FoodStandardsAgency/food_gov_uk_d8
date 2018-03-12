<?php

namespace Drupal\fsa_notify;

/**
 * Immediate message sending class.
 */
class FsaNotifyMessageImmediate extends FsaNotifyMessage {

  private $subject;

  /**
   * {@inheritdoc}
   */
  public function __construct() {
    parent::__construct();
    $this->subject = t('Immediate');
  }

  /**
   * {@inheritdoc}
   */
  protected function assemble($items) {

    foreach ($items as &$item) {

      // Variables for the Notify template.
      $item = [
        'subject' => $this->subject->render(),
        'date' => $this->date,
        'alert_items' => $item,
        'login' => $this->login_url,
        'unsubscribe' => $this->unsubscribe_url,
      ];
    }

    return $items;
  }

  /**
   * {@inheritdoc}
   */
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
