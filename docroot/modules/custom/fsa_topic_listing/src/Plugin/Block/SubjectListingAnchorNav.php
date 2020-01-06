<?php

namespace Drupal\fsa_topic_listing\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\block_content\Entity\BlockContent;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Session\AccountInterface;
use Drupal\node\Entity\Node;

/**
 * Creates 'Subject listing anchor nav' for taxonomy listing page.
 *
 * @Block(
 *  id = "subject_listing_anchor_nav",
 *  admin_label = @Translation("Subject listing anchor nav"),
 * )
 */
class SubjectListingAnchorNav extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {

    $node = \Drupal::routeMatch()->getParameter('node');
    $node = Node::load($node->id());

    $content = '';
    foreach ($node->get('field_lander_row')->referencedEntities() as $rows) {
      if (!empty($rows->field_subject_listing) && count($rows->field_subject_listing) >= 2) {
        // Build nav only if two or more listing blocks attached.
        foreach ($rows->field_subject_listing as $block) {
          // Loop through content and send for toc_api to create anchor links.
          $block_entity = BlockContent::load($block->getValue()['target_id']);
          $block_view = \Drupal::entityTypeManager()->getViewBuilder('block_content')->view($block_entity);
          $content .= \Drupal::service('renderer')->render($block_view);
        }
      }
    }

    // Landing page sidebar links need a different ToC type to term pages.
    $toc_type = $node->bundle() !== 'lander' ? 'term_group_anchors' : 'landing_page_sidebar_navigation';

    /** @var \Drupal\fsa_toc\FsaTocService $fsa_toc_service */
    $fsa_toc_service = \Drupal::service('fsa_toc.service');
    $build = $fsa_toc_service->renderAnchors($content, $toc_type);

    return $build;

  }

  /**
   * {@inheritdoc}
   */
  protected function blockAccess(AccountInterface $account) {

    // Make sure we a node is being viewed.
    $node = \Drupal::routeMatch()->getParameter('node');
    if (empty($node)) {
      return AccessResult::forbidden();
    }

    $nid = $node->id();
    // Reload the node to be sure it is correct type.
    $node = Node::load($nid);
    if (!$node->hasField('field_lander_row')) {
      return AccessResult::forbidden();
    }

    foreach ($node->get('field_lander_row')->referencedEntities() as $row) {
      if (!empty($row->field_subject_listing)) {
        return AccessResult::allowed()->addCacheableDependency($node);
      }
    }

    // And make sure if nothing to show do not bother renrering the block.
    return AccessResult::forbidden();
  }

}
