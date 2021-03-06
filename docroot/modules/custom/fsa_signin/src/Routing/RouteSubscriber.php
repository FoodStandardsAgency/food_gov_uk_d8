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
    'fsa_signin.user_preregistration',
    'fsa_signin.user_preregistration_alerts_form',
    'fsa_signin.user_preregistration_news_form',
  ];

  /**
   * Check if a redirect is required.
   */
  public function checkForRedirection(GetResponseEvent $event) {
    $request = $event->getRequest();
    $route_name = \Drupal::routeMatch()->getRouteName();

    $current_user = \Drupal::currentUser();
    $roles = $current_user->getRoles();

    $is_authenticated = \Drupal::currentUser()->isAuthenticated();

    // User is a subscriber if has only one role (authenticated)
    $is_subscriber = (count($roles) <= 1 && $roles[0] = 'authenticated') ? TRUE : FALSE;

    $preregistration_pages = self::PREREGISTRATION_ROUTES;

    // Redirect users to old site.
    if (\Drupal::state()->get('fsa_signin.redirect')) {
      if (in_array($route_name, $preregistration_pages)) {
        $url = \Drupal::state()->get('fsa_signin.external_registration_url');
        $event->setResponse(new TrustedRedirectResponse($url, 301));
      }
    }

    // Redirect /profile to the main account settings page which actually has
    // some options.
    if ($route_name == 'fsa_signin.default_controller_profilePage') {
      $url = Url::fromRoute('fsa_signin.default_controller_accountSettingsPage')->toString();
      $event->setResponse(new RedirectResponse($url, 301));
    }

    if ($is_authenticated) {
      if ($route_name == 'fsa_signin.default_controller_signInPage') {
        // No authenticated users should be able to access the signin form.
        $url = Url::fromRoute('fsa_signin.default_controller_profilePage')->toString();
        $event->setResponse(new RedirectResponse($url, 301));
      }
    }

    if ($route_name == 'user.login' || $route_name == 'user.register') {
      $url = Url::fromRoute('fsa_signin.default_controller_signInPage');
      if ($route_name == 'user.login') {
        // If accessing login via /user set a query parameter to sign in page to
        // display informative beta text.
        $url->setOption('query', ['user' => 'fsa']);
      }
      $url = $url->toString();
      $event->setResponse(new RedirectResponse($url, 301));
    }
    elseif ($route_name == 'user.pass') {
      $url = Url::fromRoute('fsa_signin.default_controller_resetPassword', [], ['query' => $request->query->all()])->toString();
      $event->setResponse(new RedirectResponse($url, 301));
    }
    elseif ($is_subscriber) {
      if ($route_name == 'entity.user.edit_form') {
        // Redirect subscriber user edit page to the custom profile manage page.
        $url = Url::fromRoute('fsa_signin.default_controller_accountSettingsPage')->toString();
        $event->setResponse(new RedirectResponse($url, 301));
      }
      if ($route_name == 'user.page' || $route_name == 'entity.user.canonical') {
        // Redirect subscribers to the custom profile page.
        $url = Url::fromRoute('fsa_signin.default_controller_profilePage')->toString();
        $event->setResponse(new RedirectResponse($url, 301));
      }
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
