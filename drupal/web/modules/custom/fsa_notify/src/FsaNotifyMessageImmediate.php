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
        'alert_items' => preg_replace('/^/m', self::NOTIFY_TEMPLATE_MESSAGE_STYLE_PREFIX, $item),
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

    $category = self::alertSubscriptionCategory($item);
    $date = self::alertDate($item);
    $line1 = sprintf('%s - %s:', $category, $date);

    $line2 = $item->getTitle();

    $link = $this->urlAlias($item, $lang);
    $more = t('Read more');
    $line3 = sprintf('%s: %s', $more, $link);

    $item = "$line1\n$line2\n$line3";
    return $item;
  }

}
