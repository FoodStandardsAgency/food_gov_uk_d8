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
    $request = $event->getRequest();
    $route_name = \Drupal::routeMatch()->getRouteName();

    $current_user = \Drupal::currentUser();
    $roles = $current_user->getRoles();

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

    // Redirect subscribers to profile manage page from the signup pages.
    if (\Drupal::currentUser()->isAuthenticated()) {
      // Add signin route to the preregistration pages array.
      $preregistration_pages[] = 'fsa_signin.default_controller_signInPage';
      if (in_array($route_name, $preregistration_pages)) {
        $url = Url::fromRoute('fsa_signin.default_controller_manageProfilePage')->toString();
        $event->setResponse(new RedirectResponse($url, 301));
      }
    }

    // Redirect Administrators/editors to their Drupal profile pages. Only
    // subscribed users should be using the profile pages.
    if (\Drupal::currentUser()->isAuthenticated() && !$is_subscriber) {
      $routes = [
        'fsa_signin.default_controller_profilePage',
        'fsa_signin.default_controller_manageProfilePage',
        'fsa_signin.delete_account_confirmation',
      ];
      if (in_array($route_name, $routes)) {
        $url = Url::fromRoute('entity.user.canonical', ['user' => $current_user->id()])->toString();
        $event->setResponse(new RedirectResponse($url, 301));
      }
    }

    // Signin/subscribe redirections.
    if ($route_name == 'fsa_signin.user_preregistration') {
      // Pre-registration "landing" page to alerts subscription.
      $url = Url::fromRoute('fsa_signin.user_preregistration_alerts_form')->toString();
      $event->setResponse(new RedirectResponse($url, 301));
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
        $url = Url::fromRoute('fsa_signin.default_controller_manageProfilePage')->toString();
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
