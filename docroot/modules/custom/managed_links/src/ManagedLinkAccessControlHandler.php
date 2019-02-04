<?php

namespace Drupal\managed_links;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Managed Link entity.
 *
 * @see \Drupal\managed_links\Entity\ManagedLink.
 */
class ManagedLinkAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\managed_links\Entity\ManagedLinkInterface $entity */
    switch ($operation) {
      case 'view':
        if (!$entity->isPublished()) {
          return AccessResult::allowedIfHasPermission($account, 'view unpublished managed link entities');
        }
        return AccessResult::allowedIfHasPermission($account, 'view published managed link entities');

      case 'update':
        return AccessResult::allowedIfHasPermission($account, 'edit managed link entities');

      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'delete managed link entities');
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add managed link entities');
  }

}
