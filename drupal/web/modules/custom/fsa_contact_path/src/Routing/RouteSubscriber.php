<?php

namespace Drupal\fsa_contact_path\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Listens to the dynamic route events.
 */
class RouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    if ($route = $collection->get('contact.site_page')) {
      $route->setPath('/feedback');
    }
    if ($route = $collection->get('entity.contact_form.canonical')) {
      $route->setPath('/feedback/{contact_form}');
    }
  }

}
