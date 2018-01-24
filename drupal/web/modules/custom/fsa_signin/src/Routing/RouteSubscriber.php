<?php

namespace Drupal\fsa_signin\Routing;

use Drupal\Core\Routing\TrustedRedirectResponse;
use Drupal\Core\Url;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Routesubscriber.
 */
class RouteSubscriber implements EventSubscriberInterface {

  const PREREGISTRATION_ROUTES = [
    'fsa_signin.user_preregistration_alerts_form',
    'fsa_signin.user_preregistration_news_form',
    'fsa_signin.user_registration_form',
  ];

  /**
   * Check if a redirect is required.
   */
  public function checkForRedirection(GetResponseEvent $event) {
    $route_name = \Drupal::routeMatch()->getRouteName();

    $preregistration_pages = self::PREREGISTRATION_ROUTES;

    // Redirect users to old site.
    // @todo: Add configuration options to enable/disable.
    if (TRUE) {
      if (in_array($route_name, $preregistration_pages)) {
        // @todo: Add configuration option for the URL.
        $url = 'https://www.food.gov.uk/about-us/subscribe';
        $event->setResponse(new TrustedRedirectResponse($url, 301));
      }
    }

    // Redirect logged in in users to profile manage page from the signup pages.
    if (\Drupal::currentUser()->isAuthenticated()) {
      // Add signin page to the array.
      $preregistration_pages[] = 'fsa_signin.default_controller_signInPage';
      if (in_array($route_name, $preregistration_pages)) {
        $url = Url::fromRoute('fsa_signin.default_controller_manageProfilePage')->toString();
        $event->setResponse(new RedirectResponse($url, 301));
      }
    }

    // Signin/subscribe redirections.
    if ($route_name == 'fsa_signin.user_preregistration') {
      // Pre-registration "landing" page to alerts subscription.
      $url = Url::fromRoute('fsa_signin.user_preregistration_alerts_form')->toString();
      $event->setResponse(new RedirectResponse($url, 301));
    }

    if ($route_name == 'user.login') {
      $url = Url::fromRoute('fsa_signin.default_controller_signInPage')->toString();
      $event->setResponse(new RedirectResponse($url, 301));
    }
    elseif ($route_name == 'user.page' || $route_name == 'entity.user.canonical') {
      $url = Url::fromRoute('fsa_signin.default_controller_profilePage')->toString();
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
