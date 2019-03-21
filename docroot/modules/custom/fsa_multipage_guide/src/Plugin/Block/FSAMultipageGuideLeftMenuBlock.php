<?php

namespace Drupal\fsa_multipage_guide\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Url;
use Drupal\entityqueue\Entity\EntityQueue;
use Drupal\fsa_multipage_guide\FSAMultiPageGuide;

/**
 * Provides the Multipage guide left hand menu block
 *
 * @Block(
 *   id = "fsa_multipage_guide_left_hand_menu_block",
 *   admin_label = @Translation("FSA Multipage Guide Left Hand Menu block"),
 *   category = @Translation("FSA"),
 * )
 */
class FSAMultipageGuideLeftMenuBlock extends BlockBase {

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

    $markup = '<div class="document-menu-wrapper document-menu-side-menu">
      <h2 class="sidebar-title">' . t('In this guide') . '</h2>
      <a href="#after-guide-side-menu2" class="skip-to-content off-canvas off-canvas--focusable">' . t('Skip this menu') . '</a>
      <nav class="document-menu">
        <ol class="document-menu__list">';

    foreach ($guide->getPages() as $page) {
      $options = ['absolute' => TRUE];
      $url = \Drupal\Core\Url::fromRoute('entity.node.canonical', ['node' => $page->id()], $options);
      $url = $url->toString();

      $markup .= '<li>';

      if ($this_page !== $page) {
        $markup .= '<a href="' . $url . '">';
      }

      $markup .= $page->getTitle();

      if ($this_page !== $page) {
        $markup .= '</a>';
      }

      $markup .= '</li>';
    }

    $markup .= '</ol></nav></div><a id="after-guide-side-menu2"></a>';

    return [
      '#markup' => $markup,
      '#cache' => [
        'max-age' => 0,
      ],
    ];
  }

}
