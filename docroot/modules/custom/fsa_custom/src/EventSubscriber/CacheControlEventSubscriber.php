<?php

namespace Drupal\fsa_custom\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

/**
 * Subscriber for adding http cache control headers; workaround
 * for removing fixed TTL, enforced 404 caching by Acquia Varnish service.
 */
class CacheControlEventSubscriber implements EventSubscriberInterface {

  /**
   * Set http cache control headers.
   */
  public function setHeaderCacheControl(FilterResponseEvent $event) {
    $response = $event->getResponse();

    if ($response->getStatusCode() != 404 || $response->isCacheable() == FALSE) {
      return;
    }

    /* Remove 404 caching enforcement. Potentially risky as it exposes the site
     * to extra 404 handling obligations from script kits or crawlers. We're relying
     * on CloudFlare DDoS protection to screen the majority of this. This
     * cache policy can likely be removed once alerts editors have access to a
     * working preview feature to prevent them prematurely triggering cached 404
     * responses from the CMS by previewing alerts before they're imported. */

    $response->headers->set('X-Acquia-No-301-404-Caching-Enforcement', 1);
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    // Response: set header content for security policy.
    $events[KernelEvents::RESPONSE][] = ['setHeaderCacheControl', -10];
    return $events;
  }

}
