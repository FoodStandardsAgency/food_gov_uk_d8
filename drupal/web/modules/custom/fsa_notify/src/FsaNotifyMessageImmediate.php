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
    $this->subject = t('FSA Update');
  }

  /**
   * {@inheritdoc}
   */
  protected function assemble($items) {

    foreach ($items as &$item) {

      // Get the alert title to subject.
      // @todo: send the actual when calling assemble(), this was a last minute request and a hack to get title for Notify subject.
      $title = explode(PHP_EOL, $item);
      $title = $title[0];

      // Variables for the Notify template.
      $item = [
        'subject' => $this->subject->render() . ': ' . $title,
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
  protected function theme($item, $lang) {
    if ($item->hasTranslation($lang)) {
      $item = $item->getTranslation($lang);
    }

    $title = $item->getTitle();
    $line1 = sprintf('%s', $title);

    $link = $this->url($item, $lang);
    $more = t('Read more');
    $line2 = sprintf('%s: %s', $more, $link);

    $item = "$line1\n$line2\n";
    return $item;
  }

}
