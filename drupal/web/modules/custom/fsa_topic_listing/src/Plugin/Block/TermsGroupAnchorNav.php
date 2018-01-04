<?php

namespace Drupal\fsa_topic_listing\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Session\AccountInterface;
use Drupal\toc_api\Entity\TocType;

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

    $build = [];

    // Get the rendered term listing content.
    $tid = $routematch->getParameter('taxonomy_term')->id();
    $embed = views_embed_view('taxonomy_term', 'page', $tid);
    $view = (string) \Drupal::service('renderer')->render($embed);

    // Get our custom "Term group anchors" TOC type options.
    /** @var \Drupal\toc_api\TocTypeInterface $toc_type */
    $toc_type = TocType::load('term_group_anchors');
    $options = ($toc_type) ? $toc_type->getOptions() : [];

    // Create TOC instance using the TOC manager.
    /** @var \Drupal\toc_api\TocManagerInterface $toc_manager */
    $toc_manager = \Drupal::service('toc_api.manager');
    /** @var \Drupal\toc_api\TocInterface $toc */
    $toc = $toc_manager->create('toc_filter', $view, $options);

    // If provided page content allows creating the toc build it.
    if ($toc->isVisible()) {
      /** @var \Drupal\toc_api\TocBuilderInterface $toc_builder */
      $toc_builder = \Drupal::service('toc_api.builder');
      $build = [
        'toc' => $toc_builder->buildToc($toc),
      ];
    }

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
