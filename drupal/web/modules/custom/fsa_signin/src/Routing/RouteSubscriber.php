<?php

namespace Drupal\fsa_signin\Routing;

use Drupal\Core\Url;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Routesubscriber.
 */
class RouteSubscriber implements EventSubscriberInterface {

  /**
   * Check if a redirect is required.
   */
  public function checkForRedirection(GetResponseEvent $event) {
    $route_name = \Drupal::routeMatch()->getRouteName();

    // Signin/subscribe redirections.
    if ($route_name == 'fsa_signin.user_preregistration') {
      // Pre-registration "langing" page to alerts subscription.
      $url = Url::fromRoute('fsa_signin.user_preregistration_alerts_form')->toString();
      $event->setResponse(new RedirectResponse($url, 301));
    }

    if ($route_name == 'user.login') {
      $url = Url::fromRoute('fsa_signin.default_controller_signInPage')->toString();
      $event->setResponse(new RedirectResponse($url, 301));
    }
    elseif ($route_name == 'user.page' || $route_name == 'entity.user.canonical') {
      $url = Url::fromRoute('fsa_signin.default_controller_emailSubscriptionsPage')->toString();
      $event->setResponse(new RedirectResponse($url, 301));
    }
    elseif ($route_name == 'user.register') {
      $url = Url::fromRoute('fsa_signin.user_preregistration_alerts_form')->toString();
      $event->setResponse(new RedirectResponse($url, 301));
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[KernelEvents::REQUEST][] = ['checkForRedirection'];
    return $events;
  }

}
