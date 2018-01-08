<?php
/**
 * @file
 * Contains \Drupal\fsa_toc\Plugin\Block\FsaTocBlock.
 */

namespace Drupal\fsa_toc\Plugin\Block;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Block\BlockBase;
use Drupal\node\Entity\Node;

/**
 * Provides a 'FSA Table of contents' block.
 *
 * @Block(
 *   id = "fsa_toc_block",
 *   admin_label = @Translation("Table of contents"),
 * )
 */
class FsaTocBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build()
  {
    $build = [];

    $node = \Drupal::routeMatch()->getParameter('node');
    $node = Node::load($node->id());

    $fsa_toc_enabled = $node->get('field_fsa_toc')->value;

    if ($fsa_toc_enabled) {

      $body = $node->body->view(['label' => 'inline']);
      $content = (string)\Drupal::service('renderer')->render($body);

      /** @var \Drupal\fsa_toc\FsaTocService $fsa_toc_service */
      $fsa_toc_service = \Drupal::service('fsa_toc.service');
      $build = $fsa_toc_service->renderAnchors($content, 'anchor_navigation');
    }

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  protected function blockAccess(AccountInterface $account) {

    $node = \Drupal::routeMatch()->getParameter('node');

    if (!$node || !$node->hasField('body') || !$node->hasField('field_fsa_toc')) {
      return AccessResult::forbidden();
    }

    // If body field contains [toc] placeholder, toc is displayed by toc_filter block already.
    if (stripos($node->body->value, '[toc') !== FALSE) {
      return AccessResult::forbidden();
    }

    return parent::blockAccess($account);
  }
}
