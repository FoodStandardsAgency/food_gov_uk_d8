<?php

namespace Drupal\fsa_multipage_guide\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Url;
use Drupal\entityqueue\Entity\EntityQueue;
use Drupal\fsa_multipage_guide\FSAMultiPageGuide;

/**
 * Provides the multi page guide footer block
 *
 * @Block(
 *   id = "fsa_multipage_guide_footer_block",
 *   admin_label = @Translation("FSA multi page Guide footer block"),
 *   category = @Translation("FSA"),
 * )
 */
class FSAMultiPageGuideFooterBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $this_page = \Drupal::routeMatch()->getParameter('node');
    $guide = FSAMultiPageGuide::GetGuideForPage($this_page);

    if (empty($guide)) {
      // This page isn't part of a guide.
      return [];
    }

    $next_page = $guide->getNextPage($this_page);
    $prev_page = $guide->getPrevPage($this_page);
    $position = $guide->getPagePosition($this_page) + 1;
    $markup = '';

    if (!empty($next_page) || !empty($prev_page)) {
      $markup .= '<nav class="next-previous"><ul class="next-previous__nav"><li class="next-previous__nav__item next-previous__nav--previous">';

      if (!empty($prev_page)) {
        $options = ['absolute' => TRUE];
        $url = \Drupal\Core\Url::fromRoute('entity.node.canonical', ['node' => $prev_page->id()], $options);
        $url = $url->toString();
        $markup .= '<a href="' . $url . '"><span class="next-previous__previous">' . t('Previous') . '</span><p class="next-previous__type">' . ($position - 1) . '. ' . $prev_page->getTitle() . '</p></a>';
      }

      $markup .= ' </li><li class="next-previous__nav__item next-previous__nav--next">';

      if (!empty($next_page)) {
        $options = ['absolute' => TRUE];
        $url = \Drupal\Core\Url::fromRoute('entity.node.canonical', ['node' => $next_page->id()], $options);
        $url = $url->toString();
        $markup .= '<a href="' . $url . '"><span class="next-previous__next">' . t('Next') . '</span><p class="next-previous__type">' . ($position + 1) . '. ' . $next_page->getTitle() . '</p></a>';
      }

      $markup .= '</li></ul></nav>';
    }

    if ($guide->hasPages()) {
      $markup .= '<div class="document-menu-wrapper"><nav class="document-menu">
        <h3 class="document-menu__heading">' . t('In this Guide') . '</h3>
        <a href="#after-guide-footer-menu" class="skip-to-content off-canvas off-canvas--focusable">' . t('Skip this menu') . '</a>
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

      $markup .= '</ol></nav><a id="after-guide-footer-menu"></a><div class="content-toolbar">
            <div class="content-toolbar__item content-toolbar__item--pdf">
                <a href="' . $guide->getPDFExportUrl() .  '" class="print__link--pdf" target="_blank">' . t('View entire guide as PDF') . '</a>
            </div>
            <div class="content-toolbar__item content-toolbar__item--print">
                <a href="' . $guide->getPrintUrl() . '" class="page-print-trigger" target="_blank">' . t('Print guide') . '</a>
            </div>
            </div></div>';
    }

    return [
      '#markup' => $markup,
      '#cache' => [
        'max-age' => 0,
      ],
    ];
  }

}
