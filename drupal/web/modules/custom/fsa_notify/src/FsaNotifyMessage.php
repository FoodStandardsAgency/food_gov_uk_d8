<?php

namespace Drupal\fsa_notify;

use Drupal\Core\Url;
use Drupal\node\Entity\Node;

/**
 * Defines base implementation for FSA Notify messaging.
 */
abstract class FsaNotifyMessage {

  const NOTIFY_TEMPLATE_MESSAGE_STYLE_PREFIX = '^ ';

  protected static $cache = [];
  protected static $base_url;
  protected static $login_url;
  protected static $unsubscribe_url;
  protected static $date;

  /**
   * Construct the object.
   */
  public function __construct() {

    // Sending is done via cron, hardcode domain for links based on WKV_SITE_ENV
    // if/when cron is triggered without --uri flag to avoid the links being
    // created as http://default/....
    switch (getenv("WKV_SITE_ENV")) {
      case 'local':
        $base_url = 'https://local.food.gov.uk';
        break;

      case 'development':
        $base_url = 'https://fsa.dev.wunder.io';
        break;

      case 'stage':
        $base_url = 'https://fsa.stage.wunder.io';
        break;

      default:
        $base_url = 'https://www.food.gov.uk';
        break;
    }

    $this->base_url = $base_url;

    $url = Url::fromRoute('fsa_signin.default_controller_signInPage', []);
    $this->login_url = $base_url . $url->toString();

    $url = Url::fromRoute('fsa_signin.default_controller_unsubscribe', []);
    $this->unsubscribe_url = $base_url . $url->toString();

    $this->date = date('j F Y');
  }

  /**
   * Todo: document.
   */
  public function format($nids, $lang) {
    sort($nids, SORT_NUMERIC);
    $items = [];
    foreach ($nids as $nid) {
      $node = Node::load($nid);
      if (empty($this->cache[$nid]) && is_object($node)) {
        if ($node->hasTranslation($lang)) {
          $node = $node->getTranslation($lang);
        }
        $this->cache[$nid] = $this->theme($node, $lang);
      }
      $items[] = $this->cache[$nid];
    }
    $data = $this->assemble($items);
    return $data;
  }

  /**
   * Todo: document.
   */
  abstract protected function theme($item, $lang);

  /**
   * Todo: document.
   */
  abstract protected function assemble($items);

  /**
   * Get short url for message links.
   *
   * @param \Drupal\node\Entity\Node $node
   *   The node object.
   * @param string $lang
   *   Language code.
   *
   * @return string
   *   Absolute short URL.
   */
  protected function url(Node $node, $lang) {

    $prefix = FALSE;
    if ($lang === 'cy') {
      $prefix = '/' . $lang;
    }

    $nid = $node->id();
    $url = sprintf('%s%s/node/%d', $this->base_url, $prefix, $nid);
    return $url;
  }

  /**
   * Get aliased node url for message links.
   *
   * @param \Drupal\node\Entity\Node $node
   *   The node object.
   * @param string $lang
   *   Language code.
   *
   * @return string
   *   Aliased, absolute URL.
   */
  protected function urlAlias(Node $node, $lang) {

    $prefix = FALSE;
    if ($lang === 'cy') {
      $prefix = '/' . $lang;
    }

    $url = $this->base_url . $prefix . \Drupal::service('path.alias_manager')->getAliasByPath('/node/' . $node->id(), $lang);
    return $url;
  }

  /**
   * Get the value of SMStext API content (field_alert_smstext).
   *
   * @param \Drupal\node\Entity\Node $node
   *   The node object.
   *
   * @return string
   *   Value of field_alert_smstext.
   */
  protected function smsText(Node $node) {
    if ($node->hasField('field_alert_smstext') && $node->field_alert_smstext->value != '') {
      $message = $node->field_alert_smstext->value;
    }
    else {
      // Fallback in case field is removed.
      $message = $node->getTitle();
    }
    return $message;
  }

  /**
   * Get node update timestamp to alerts.
   *
   * @param \Drupal\node\Entity\Node $node
   *   The node object.
   * @param string $format
   *   Time display format.
   *
   * @return string
   *   Formatted display of node changed timestamp.
   */
  public function alertDate(Node $node, $format = 'medium') {
    $date = \Drupal::service('date.formatter')->format($node->getChangedTime(), $format);

    return $date;
  }

  /**
   * Get alert subcription category from node.
   *
   * @param \Drupal\node\Entity\Node $node
   *   The node object.
   *
   * @return string
   *   The alert type/category of the node.
   */
  public function alertSubscriptionCategory(Node $node) {

    if ($node->hasField('field_alert_type')) {
      switch ($node->field_alert_type->value) {
        case 'AA':
          $category = t('Allergy alert');
          break;

        default:
          $category = t('Food alert');
      }
    }
    else {
      switch ($node->getType()) {
        case 'news':
          $category = t('News update');
          break;

        case 'consultation':
          $category = t('Consultation update');
          break;

        default:
          // Default to whatever the node type machine name is.
          $category = ucfirst($node->getType());
      }

    }

    return $category;
  }

}
