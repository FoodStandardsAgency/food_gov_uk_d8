<?php

namespace Drupal\fsa_workflows\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;
use Drupal\fsa_workflows\Access\MediaAccessCheck;

class RouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    if ($route = $collection->get('entity.media.add_page')) {
      $route->setRequirement(
        '_custom_access',
        MediaAccessCheck::class . '::access'
      );
    }
  }

}