<?php

namespace Drupal\fsa_ratings\Routing;

use Drupal\Core\Routing\TrustedRedirectResponse;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Routesubscriber.
 */
class RouteSubscriber implements EventSubscriberInterface {

  const RATING_ROUTES = [
    'fsa_ratings.ratings_search',
    'fsa_ratings.ratings_meanings',
    'entity.fsa_establishment.canonical',
    'entity.fsa_authority.canonical',
    'view.search_global_ratings.page_1',
  ];

  /**
   * Check if a redirect is required.
   */
  public function checkForRedirection(GetResponseEvent $event) {

    $rating_pages = self::RATING_ROUTES;
    $url = 'http://ratings.food.gov.uk/';

    $route_name = \Drupal::routeMatch()->getRouteName();

    $current_user = \Drupal::currentUser();
    $roles = $current_user->getRoles();

    // Redirect non-admin users to old rating site.
    if (\Drupal::state()->get('fsa_ratings.decoupled')) {
      if (in_array($route_name, $rating_pages)) {
        if (!in_array('administrator', $roles)) {
          $event->setResponse(new TrustedRedirectResponse($url, 301));
        }
        else {
          drupal_set_message(t('Ratings content is decoupled, visitors are redirected to <a href="@url">@url</a>', ['@url' => $url]), 'warning');
        }
      }
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
