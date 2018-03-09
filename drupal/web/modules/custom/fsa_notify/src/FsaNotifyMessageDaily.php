<?php

namespace Drupal\fsa_notify;

/**
 * Daily message digest sending class.
 */
class FsaNotifyMessageDaily extends FsaNotifyMessage {

  private $subject;

  /**
   * {@inheritdoc}
   */
  public function __construct() {
    parent::__construct();
    $this->subject = t('Daily digest');
  }

  /**
   * {@inheritdoc}
   */
  protected function assemble($items) {

    $items = implode("\n", $items);

    $items = [[
      'subject' => $this->subject->render(),
      'date' => $this->date,
      'allergy_alerts' => $items,
      'login' => $this->login_url,
      'unsubscribe' => $this->unsubscribe_url,
    ],
    ];

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
