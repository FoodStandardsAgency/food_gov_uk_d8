<?php

namespace Drupal\fsa_topic_listing\Plugin\Block;

use Drupal\block_content\Entity\BlockContent;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Session\AccountInterface;
use Drupal\node\Entity\Node;
use Drupal\toc_api\Entity\TocType;

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
    $build = [];
    $node = \Drupal::routeMatch()->getParameter('node');
    $node = Node::load($node->id());
    $view = '';
    foreach ($node->get('field_lander_row')->referencedEntities() as $rows) {
      if (!empty($rows->field_subject_listing) && count($rows->field_subject_listing) >= 2) {
        // Build nav only if two or more listing blocks attached.
        foreach ($rows->field_subject_listing as $block) {
          // Loop through content and send for toc_api to create anchor links.
          $block_entity = BlockContent::load($block->getValue()['target_id']);
          $block_view = \Drupal::entityTypeManager()->getViewBuilder('block_content')->view($block_entity);
          $view .= \Drupal::service('renderer')->render($block_view);
        }
      }
    }

    // @todo: We are repeating here; move toc creation into a service/controller and reuse form TermsGroupAnchorNav.php
    // Get custom "Term group anchors" TOC type options.
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
