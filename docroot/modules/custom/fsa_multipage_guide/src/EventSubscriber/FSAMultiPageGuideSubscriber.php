<?php

namespace Drupal\fsa_multipage_guide\EventSubscriber;

use Drupal\fsa_multipage_guide\FSAMultiPageGuide;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class FSAMultiPageGuideSubscriber implements EventSubscriberInterface {

  public function redirectMultiPageGuideToFirstPage(GetResponseEvent $event) {
    $parameters = \Drupal::routeMatch()->getParameters();

    if ($parameters->has('export_type')) {
      // PDF export in progress, do not redirect.
      return;
    }

    $print_query = $event->getRequest()->query->get('print');

    if (!empty($print_query)) {
      // The special print query parameter means we want to print the page.
      return;
    }

    if ($parameters->has('node')) {
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
