<?php

namespace Drupal\fsa_topic_listing\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Session\AccountInterface;

/**
 * Creates 'Term group nav' for taxonomy listing page.
 *
 * @Block(
 *  id = "terms_group_anchor_nav",
 *  admin_label = @Translation("Term group anchor nav"),
 * )
 */
class TermsGroupAnchorNav extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $routematch = \Drupal::routeMatch();

    // Get the rendered term listing content.
    $tid = $routematch->getParameter('taxonomy_term')->id();
    $embed = views_embed_view('taxonomy_term', 'page', $tid);
    $content = (string) \Drupal::service('renderer')->render($embed);

    /** @var \Drupal\fsa_toc\FsaTocService $fsa_toc_service */
    $fsa_toc_service = \Drupal::service('fsa_toc.service');
    $build = $fsa_toc_service->renderAnchors($content, 'term_group_anchors');

    return $build;

  }

  /**
   * {@inheritdoc}
   */
  protected function blockAccess(AccountInterface $account) {

    $vid = \Drupal::routeMatch()->getParameter('taxonomy_term');

    if (!isset($vid)) {
      // No term parameter, do not render the block.
      return AccessResult::forbidden();
    }
    elseif ($vid->getVocabularyId() != 'topic') {
      // Other than topic vocab, do not render the block.
      return AccessResult::forbidden();
    }
    else {
      return AccessResult::allowed();
    }
  }

}
