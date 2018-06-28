<?php

namespace Drupal\fsa_managed_links;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the FSA managed link entity.
 *
 * @see \Drupal\fsa_managed_links\Entity\FsaManagedLink.
 */
class FsaManagedLinkAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\fsa_managed_links\Entity\FsaManagedLinkInterface $entity */
    switch ($operation) {
      case 'view':
        return AccessResult::allowedIfHasPermission($account, 'view fsa managed link entities');

      case 'update':
        return AccessResult::allowedIfHasPermission($account, 'edit fsa managed link entities');

      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'delete fsa managed link entities');
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add fsa managed link entities');
  }

}
