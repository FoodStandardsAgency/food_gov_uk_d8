<?php

namespace Drupal\fsa_workflows\Access;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\Routing\Route;
use Drupal\Core\Routing\RouteMatchInterface;

class MediaAccessCheck implements AccessInterface {

  public function access(Route $route, RouteMatchInterface $route_match, AccountInterface $account) {

    $roles = $account->getRoles();

    // Disable Add Media button for users who have the single "Author" role.
    if (in_array('author', $roles) && !in_array('editor', $roles)) {
      return AccessResult::forbidden();
    }

    return AccessResult::allowed();
  }
}