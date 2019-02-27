<?php

namespace Drupal\fsa_multipage_guide\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Url;
use Drupal\entityqueue\Entity\EntityQueue;
use Drupal\fsa_multipage_guide\FSAMultiPageGuide;

/**
 * Provides the multi page guide title block above all pages that are part of
 * a guide.
 *
 * @Block(
 *   id = "fsa_multipage_guide_title_block",
 *   admin_label = @Translation("FSA multi page guide title block"),
 *   category = @Translation("FSA"),
 * )
 */
class FSAMultipageGuideTitleBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $this_page = \Drupal::routeMatch()->getParameter('node');
    $guide = FSAMultiPageGuide::GetGuideForPage($this_page);

    if (empty($guide)) {
      // This page isn't part of a guide.
      return array();
    }

    return [
      '#markup' => '<h1>' . $guide->getTitle() . '</h1>'
    ];
  }

}
