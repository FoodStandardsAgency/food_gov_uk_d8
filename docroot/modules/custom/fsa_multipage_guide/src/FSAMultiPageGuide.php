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
    return !empty($entity) && $entity instanceof \Drupal\node\NodeInterface && $entity->bundle() === self::MULTIPAGE_GUIDE_BUNDLE;
  }

  public static function IsPage(\Drupal\Core\Entity\EntityInterface $entity) {
    return !empty($entity) && $entity instanceof \Drupal\node\NodeInterface && $entity->bundle() === self::PAGE_BUNDLE;
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
   * @param \Drupal\node\NodeInterface $guide
   *
   * @return \Drupal\fsa_multipage_guide\FSAMultiPageGuide
   */
  public static function Get($guide) {
    if (is_numeric($guide)) {
      $guide = \Drupal\node\Entity\Node::load($guide);
    }

    $guides = [];
    if (self::IsGuide($guide)) {
      if (empty($guides[$guide->id()])) {
        $guides[$guide->id()] = new FSAMultiPageGuide($guide);
      }
      return $guides[$guide->id()];
    }
  }

  /**
   * FSAMultipageGuide constructor.
   *
   * Don't call directly, use the factory function
   * $guide = FSAMultiPageGuide::Get($node);
   *
   * @param \Drupal\node\NodeInterface $guide
   */
  public function __construct(NodeInterface $guide) {
    $this->guide = $guide;
  }

  /**
   * Get the current guide object in the right language.
   *
   * @param string $lang_code
   *   Optional, specify the language to return the guide in, otherwise select
   *   the user's current language.
   *
   * @return \Drupal\node\NodeInterface
   */
  public function getGuide($lang_code = '') {
    if (empty($lang_code)) {
      $lang_code = \Drupal::languageManager()->getCurrentLanguage()->getId();
    }
    return $this->guide->getTranslation($lang_code);
  }

  /**
   * @return int
   */
  public function getId() {
    return $this->guide->id();
  }

  /**
   * @return string
   */
  public function getTitle() {
    return $this->getGuide()->getTitle();
  }

  /**
   * @return \Drupal\node\NodeInterface[]
   */
  public function getPages($access_check = TRUE) {
    $pages = $this->getGuide()->field_guide_pages->referencedEntities();
    $lang_code = \Drupal::languageManager()->getCurrentLanguage()->getId();
    $translated_pages = array_map(function($page) use ($lang_code) {
      return $page->getTranslation($lang_code);
    }, $pages);

    if (!$access_check) {
      return $translated_pages;
    }

    $access_pages = [];

    foreach ($translated_pages as $page) {
      if ($page->access('view') === TRUE) {
        $access_pages[] = $page;
      }
    }

    return $access_pages;
  }

  /**
   * @return bool
   */
  public function hasPages() {
    return count($this->getPages()) > 0;
  }

  /**
   * @return \Drupal\node\NodeInterface|null
   */
  public function getFirstPage() {
    $pages = $this->getPages();
    return !empty($pages) ? $pages[0] : NULL;
  }

  /**
   * @param \Drupal\node\NodeInterface $page
   *
   * @return \Drupal\node\NodeInterface
   */
  public function getNextPage(NodeInterface $page) {
    $pages = $this->getPages();
    $position = $this->getPagePosition($page);

    if ($position !== FALSE && count($pages) > $position + 1) {
      return $pages[$position + 1];
    }
  }

  /**
   * @param \Drupal\node\NodeInterface $page
   *
   * @return \Drupal\node\NodeInterface
   */
  public function getPrevPage(NodeInterface $page) {
    $position = $this->getPagePosition($page);

    if (!empty($position)) {
      $pages = $this->getPages();
      return $pages[$position - 1];
    }
  }

  /**
   * @param \Drupal\node\NodeInterface $page
   *
   * @return \Drupal\node\NodeInterface|FALSE
   *   A numeric position in the list starting at 0 or FALSE if not in the guide.
   */
  public function getPagePosition(NodeInterface $page) {
    return array_search($page, $this->getPages());
  }

  /**
   * Get the entity print url for this guide.
   *
   * @return string
   */
  public function getPDFExportUrl() {
    $lang_code = \Drupal::languageManager()->getCurrentLanguage()->getId();
    $pdf_export_url = '/print/pdf/node/' . $this->getId();

    if ($lang_code !== 'en') {
      $pdf_export_url = '/' . $lang_code . $pdf_export_url;
    }

    return $pdf_export_url;
  }

  /**
   * Get the print url for the guide.
   *
   * @return string
   */
  public function getPrintUrl() {
    $lang_code = \Drupal::languageManager()->getCurrentLanguage()->getId();
    $print_url = '/node/' . $this->getId() . '?print=1';

    if ($lang_code !== 'en') {
      $print_url = '/' . $lang_code . $print_url;
    }

    return $print_url;
  }

  /**
   * Get the first page url.
   *
   * @return string
   */
  public function getFirstPageUrl() {
    $first_page = $this->getFirstPage();

    if (!empty($first_page)) {
      $lang_code = \Drupal::languageManager()->getCurrentLanguage()->getId();

      $url = '/node/' . $first_page->id();
      if ($lang_code !== 'en') {
        $url = '/' . $lang_code . $url;
      }

      return $url;
    }
  }

}
