<?php

/**
 * @file
 * Utility functions for the Multipage guide.
 */

namespace Drupal\fsa_multipage_guide;

use \Drupal\node\NodeInterface;

class FSAMultiPageGuide {

  const MULTIPAGE_GUIDE_BUNDLE = 'multipage_guide';
  const PAGE_BUNDLE = 'page';

  /** @var NodeInterface $guide */
  public $guide;

  public static function IsGuide(\Drupal\Core\Entity\EntityInterface $entity) {
    return $entity instanceof \Drupal\node\NodeInterface && $entity->bundle() === self::MULTIPAGE_GUIDE_BUNDLE;
  }

  public static function IsPage(\Drupal\Core\Entity\EntityInterface $entity) {
    return $entity instanceof \Drupal\node\NodeInterface && $entity->bundle() === self::PAGE_BUNDLE;
  }

  public static function GetGuideForPage($page) {
    if (self::IsPage($page)) {
      $nids = \Drupal::entityQuery('node')
        ->condition('type', self::MULTIPAGE_GUIDE_BUNDLE)
        ->condition('field_guide_pages.target_id', $page->id())
        ->execute();

      if (is_array($nids)) {
        $nid = reset($nids);
        return !empty($nid) ? self::Get(\Drupal\node\Entity\Node::load($nid)) : NULL;
      }
    }
  }

  /**
   * @param $guide
   *
   * @return \Drupal\fsa_multipage_guide\FSAMultiPageGuide
   */
  public static function Get($guide) {
    if (self::isGuide($guide)) {
      return new FSAMultiPageGuide($guide);
    }
  }

  /**
   * FSAMultipageGuide constructor.
   *
   * @param \Drupal\node\NodeInterface $guide
   */
  public function __construct(NodeInterface $guide) {
    $this->guide = $guide;
  }

  public function getId() {
    return $this->guide->id();
  }

  /**
   * @return \Drupal\node\NodeInterface[]
   */
  public function getPages() {
    return $this->guide->field_guide_pages->referencedEntities();
  }

  /**
   * @return \Drupal\node\NodeInterface|null
   */
  public function getFirstPage() {
    $pages = $this->getPages();
    return !empty($pages) ? $pages[0] : NULL;
  }

}
