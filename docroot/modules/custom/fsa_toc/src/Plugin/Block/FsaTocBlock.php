<?php

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
  public function build() {
    $langcode = \Drupal::languageManager()->getCurrentLanguage()->getId();

    $node = \Drupal::routeMatch()->getParameter('node');
    $node = Node::load($node->id());

    if ($node->hasTranslation($langcode)) {
      $node = $node->getTranslation($langcode);
    }

    $fsa_toc_enabled = $node->get('field_fsa_toc')->value;

    if ($fsa_toc_enabled) {
      $body = $node->body;
      $body = $body->view(['label' => 'inline']);

      // We must render this instead of getting body->value, because h2 anchors
      // are built on the fly via filter.
      $content = (string) \Drupal::service('renderer')->render($body);

      /** @var \Drupal\fsa_toc\FsaTocService $fsa_toc_service */
      $fsa_toc_service = \Drupal::service('fsa_toc.service');
      $toc = $fsa_toc_service->renderAnchors($content, 'anchor_navigation');
      if (!empty($toc)) {
        return $toc;
      }
    }

    return [];
  }

  /**
   * {@inheritdoc}
   */
  protected function blockAccess(AccountInterface $account) {
    $node = \Drupal::routeMatch()->getParameter('node');

    if (empty($node)) {
      return AccessResult::forbidden();
    }

    $langcode = \Drupal::languageManager()->getCurrentLanguage()->getId();

    if ($node->hasTranslation($langcode)) {
      $node = $node->getTranslation($langcode);
    }

    if (!is_object($node) || !$node->hasField('body') || !$node->hasField('field_fsa_toc')) {
      return AccessResult::forbidden();
    }

    // If body field contains [toc] placeholder, toc is displayed by
    // toc_filter block already.
    if (stripos($node->body->value, '[toc') !== FALSE) {
      return AccessResult::forbidden();
    }

    // If there is a way to remove empty block from region, feel free to
    // remove lines below. Otherwise - doubleprocessing here and in build.
    $fsa_toc_enabled = $node->get('field_fsa_toc')->value;
    if (!$fsa_toc_enabled) {
      return AccessResult::forbidden();
    }

    /** @var \Drupal\fsa_toc\FsaTocService $fsa_toc_service */
    $fsa_toc_service = \Drupal::service('fsa_toc.service');
    $toc = $fsa_toc_service->renderAnchors($node->body->value, 'anchor_navigation');
    if (empty($toc)) {
      return AccessResult::forbidden();
    }

    return parent::blockAccess($account);
  }

}
