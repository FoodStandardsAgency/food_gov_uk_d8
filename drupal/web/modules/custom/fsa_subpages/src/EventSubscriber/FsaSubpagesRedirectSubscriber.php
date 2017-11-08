<?php

namespace Drupal\fsa_subpages\EventSubscriber;

use Drupal\Core\Url;
use Drupal\node\Entity\Node;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Redirect event subscriber.
 */
class FsaSubpagesRedirectSubscriber implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return([
      KernelEvents::REQUEST => [
        ['redirectSubpagesNode'],
      ],
    ]);
  }

  /**
   * Redirect requests for node with Sub-pages to first subpage.
   *
   * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
   *   Event.
   */
  public function redirectSubpagesNode(GetResponseEvent $event) {

    $request = $event->getRequest();

    // Dont redirect on non-node-view pages.
    $route = \Drupal::routeMatch()->getRouteName();
    if ($route != 'entity.node.canonical') {
      return;
    }

    // Make sure we have the node.
    $node = \Drupal::routeMatch()->getParameter('node');
    if (empty($node)) {
      // Cannot happen.
      // what should be done in this case?
      // fallback: let system handle the situation.
      return;
    }

    $nid = $node->id();
    // Reload the node to be sure it is correct type.
    $node = Node::load($nid);
    if (!$node->hasField('field_subpages')) {
      return;
    }
    $paragraphs = $node->get('field_subpages')->referencedEntities();
    if (empty($paragraphs)) {
      return;
    }

    // Build a list of valid sub-page aliases.
    $aliases = [];
    foreach ($paragraphs as $p) {
      $alias = $p->get('field_url_alias')->getString();
      $aliases[$alias] = TRUE;
    }

    // Get URL query key-value pairs.
    $param = \Drupal::request()->query->all();

    // Check if query contains something that matches any sub-page alias.
    $match = array_intersect_key($aliases, $param);
    if (!empty($match)) {
      return;
    }

    // Do the redirect
    // be careful, or you get redirect loop.
    $route = 'entity.node.canonical';
    $params = ['node' => $nid];
    $alias = $paragraphs[0]->get('field_url_alias')->getString();
    $options = ['query' => [$alias => NULL]];
    $url = Url::fromRoute($route, $params, $options);
    $url = $url->toString();
    $response = new RedirectResponse($url, 301);
    $event->setResponse($response);
  }

}
