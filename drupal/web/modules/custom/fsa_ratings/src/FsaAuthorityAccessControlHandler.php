<?php

namespace Drupal\fsa_ratings;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the FSA Authority entity.
 *
 * @see \Drupal\fsa_ratings\Entity\FsaAuthority.
 */
class FsaAuthorityAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\fsa_ratings\Entity\FsaAuthorityInterface $entity */
    switch ($operation) {
      case 'view':
        if (!$entity->isPublished()) {
          return AccessResult::allowedIfHasPermission($account, 'view unpublished fsa authority entities');
        }
        return AccessResult::allowedIfHasPermission($account, 'view published fsa authority entities');

      case 'update':
        return AccessResult::allowedIfHasPermission($account, 'edit fsa authority entities');

      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'delete fsa authority entities');
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add fsa authority entities');
  }

}
