<?php

namespace Drupal\fsa_notify;

use Drupal\Core\Url;
use Drupal\node\Entity\Node;

/**
 * Defines base implementation for FSA Notify messaging.
 */
abstract class FsaNotifyMessage {

  protected static $cache = [];
  protected static $base_url;
  protected static $login_url;
  protected static $unsubscribe_url;
  protected static $date;

  /**
   * Construct the object.
   */
  public function __construct() {

    $url = \Drupal::request()->getSchemeAndHttpHost();
    $this->base_url = $url;

    $url = Url::fromRoute('user.login', [], ['absolute' => TRUE]);
    $url = $url->toString();
    $this->login_url = $url;

    $url = 'http://.../unsubscribe';
    $this->unsubscribe_url = $url;

    $this->date = date('j F Y');
  }

  /**
   * Todo: document.
   */
  public function format($nids) {
    sort($nids, SORT_NUMERIC);
    $items = [];
    foreach ($nids as $nid) {
      $node = Node::load($nid);
      if (empty($this->cache[$nid])) {
        $this->cache[$nid] = $this->theme($node);
      }
      $items[] = $this->cache[$nid];
    }
    $data = $this->assemble($items);
    return $data;
  }

  /**
   * Todo: document.
   */
  abstract protected function theme($item);

  /**
   * Todo: document.
   */
  abstract protected function assemble($items);

  /**
   * Generate "short" for nodes in messages.
   */
  protected function url($node) {
    $nid = $node->id();
    $url = sprintf('%s/node/%d', $this->base_url, $nid);
    return $url;
  }

}
