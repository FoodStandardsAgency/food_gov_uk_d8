<?php

namespace Drupal\fsa_notify;

use Drupal\Core\Url;
use Drupal\node\Entity\Node;

/**
 * Defines base implementation for FSA Notify messaging.
 */
abstract class FsaNotifyMessage {

  protected static $cache = [];
  protected static $date;

  const NOTIFY_TEMPLATE_MESSAGE_STYLE_PREFIX = '^ ';

  // Define plaheholder names for custom strings in alert_items content that
  // should be converted into translated strings for user language.
  const ST_READMORE = '[READMORE]';
  const ST_ALL_CAT_AA = '[CAT_AA]';
  const ST_ALL_CAT_FA = '[CAT_FA]';
  const ST_TYPE_CAT_NU = '[CAT_NU]';
  const ST_TYPE_CAT_CU = '[CAT_CU]';

  /**
   * Construct the object.
   */
  public function __construct() {
    $this->date = date('j F Y');
  }

  /**
   * Get the base url of current environment.
   *
   * @return string
   *   The site base url.
   */
  public static function baseUrl() {

    // Sending is done via cron, hardcode domain for links based on WKV_SITE_ENV
    // if/when cron is triggered without --uri flag to avoid the links being
    // created as http://default/....
    // @todo: fsa_content_reminder_cron() duplicates this logic, consider moving into a service.
    switch (getenv("WKV_SITE_ENV")) {
      case 'local':
        $baseUrl = 'https://local.food.gov.uk';
        break;

      case 'development':
        $baseUrl = 'https://fsa.dev.wunder.io';
        break;

      case 'stage':
        $baseUrl = 'https://fsa.stage.wunder.io';
        break;

      default:
        $baseUrl = 'https://www.food.gov.uk';
        break;
    }

    return $baseUrl;
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
    $url = sprintf('%s%s/node/%d', FsaNotifyMessage::baseUrl(), $prefix, $nid);
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

    $url = self::baseUrl() . $prefix . \Drupal::service('path.alias_manager')->getAliasByPath('/node/' . $node->id(), $lang);
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

    $lang = $node->language()->getId();

    if ($node->hasField('field_alert_type')) {
      switch ($node->field_alert_type->value) {
        case 'AA':
          $category = self::ST_ALL_CAT_AA;
          break;

        default:
          $category = self::ST_ALL_CAT_FA;
      }
    }
    else {
      switch ($node->getType()) {
        case 'news':
          $category = self::ST_TYPE_CAT_NU;
          break;

        case 'consultation':
          $category = self::ST_TYPE_CAT_CU;
          break;

        default:
          // Default to whatever the node type machine name is.
          $category = ucfirst($node->getType());
      }

    }

    return $category;
  }

  /**
   * Converts placeholder string to a translated text.
   *
   * @param string $string
   *   The string to replace and translate.
   * @param string $lang
   *   Language code to translate the string to.
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup
   *   The replaced string in users preferred language.
   */
  public static function convertPlaceholder($string, $lang) {

    switch ($string) {
      case self::ST_READMORE:
        $string = t('Read more', [], ['langcode' => $lang]);
        break;

      case self::ST_ALL_CAT_AA:
        $string = t('Allergy alert', [], ['langcode' => $lang]);
        break;

      case self::ST_ALL_CAT_FA:
        $string = t('Food alert', [], ['langcode' => $lang]);
        break;

      case self::ST_TYPE_CAT_NU:
        $string = t('News update', [], ['langcode' => $lang]);
        break;

      case self::ST_TYPE_CAT_CU:
        $string = t('Consultation update', [], ['langcode' => $lang]);
        break;
    }

    return $string;

  }

  /**
   * Translates placeholder strings from a text.
   *
   * @param string $text
   *   Text to replace the placeholders from.
   * @param string $lang
   *   Language to return the replaced strings in.
   *
   * @return mixed
   *   Translated text.
   */
  public static function translatePlaceholders($text, $lang) {

    $st_search = [
      self::ST_READMORE,
      self::ST_ALL_CAT_AA,
      self::ST_ALL_CAT_FA,
      self::ST_TYPE_CAT_NU,
      self::ST_TYPE_CAT_CU,
    ];
    $st_replace = [
      self::convertPlaceholder(self::ST_READMORE, $lang),
      self::convertPlaceholder(self::ST_ALL_CAT_AA, $lang),
      self::convertPlaceholder(self::ST_ALL_CAT_FA, $lang),
      self::convertPlaceholder(self::ST_TYPE_CAT_NU, $lang),
      self::convertPlaceholder(self::ST_TYPE_CAT_CU, $lang),
    ];
    return str_replace($st_search, $st_replace, $text);
  }

}
