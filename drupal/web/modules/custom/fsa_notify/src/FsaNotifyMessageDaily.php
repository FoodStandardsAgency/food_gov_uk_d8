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
    $this->subject = t('FSA daily digest update');
  }

  /**
   * {@inheritdoc}
   */
  protected function assemble($items) {

    $items = implode("\n", $items);

    // Variables for the Notify template.
    $items = [
      [
        'subject' => $this->subject->render(),
        'date' => $this->date,
        'alert_items' => preg_replace('/^/m', self::NOTIFY_TEMPLATE_MESSAGE_STYLE_PREFIX, $items),
        'login' => $this->loginUrl,
        'unsubscribe' => $this->unsubscribeUrl,
      ],
    ];

    return $items;
  }

  /**
   * {@inheritdoc}
   */
  protected function theme($item, $lang) {
    if ($item->hasTranslation($lang)) {
      $item = $item->getTranslation($lang);
    }

    $category = self::alertSubscriptionCategory($item);
    $date = self::alertDate($item);
    $line1 = sprintf('%s - %s:', $category, $date);

    $line2 = $item->getTitle();

    $link = $this->urlAlias($item, $lang);
    $more = t('Read more');
    $line3 = sprintf('%s: %s', $more, $link);

    $item = "$line1\n$line2\n$line3\n";
    return $item;
  }

}
