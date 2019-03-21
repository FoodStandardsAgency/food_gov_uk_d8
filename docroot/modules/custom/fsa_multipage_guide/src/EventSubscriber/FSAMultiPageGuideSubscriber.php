<?php

namespace Drupal\fsa_multipage_guide\EventSubscriber;

use Drupal\fsa_multipage_guide\FSAMultiPageGuide;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class FSAMultiPageGuideSubscriber implements EventSubscriberInterface {

  /**
   * Check if the request is for a multi page guide and issue a redirect
   * to it's first page.
   *
   * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
   */
  public function redirectMultiPageGuideToFirstPage(GetResponseEvent $event) {
    $path = \Drupal::service('path.current')->getPath();
    $is_node_view = preg_match('/^\/node\/[0-9]+$/', $path) === 1;

    if (!$is_node_view) {
      // This function is only concerned with the rendered view of a node.
      return;
    }

    $print_query = $event->getRequest()->query->get('print');

    if (!empty($print_query)) {
      // The special print query parameter means we want to print the page.
      return;
    }

    $parameters = \Drupal::routeMatch()->getParameters();
    if ($parameters->has('node')) {
      // Finally check that the node is a guide and it has a first page to
      // redirect to.
      $node = $parameters->get('node');
      $guide = FSAMultiPageGuide::Get($node);

      if (!empty($guide) && $first_page_url = $guide->getFirstPageUrl()) {
        $event->setResponse(new RedirectResponse($first_page_url));
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[KernelEvents::REQUEST][] = array('redirectMultiPageGuideToFirstPage');
    return $events;
  }

}
