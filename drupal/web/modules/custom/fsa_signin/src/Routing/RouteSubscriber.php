<?php

namespace Drupal\fsa_signin\Routing;


use Drupal\Core\Url;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class RouteSubscriber implements EventSubscriberInterface {

  public function checkForRedirection(GetResponseEvent $event) {
    $route_name = \Drupal::routeMatch()->getRouteName();
    if ($route_name == 'user.login') {
      $url = Url::fromRoute('fsa_signin.default_controller_signInPage')->toString();
      $event->setResponse(new RedirectResponse($url, 301));
    }

  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[KernelEvents::REQUEST][] = array('checkForRedirection');
    return $events;
  }

}
