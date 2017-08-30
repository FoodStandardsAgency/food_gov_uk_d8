<?php

/**
 * @file
 * Contains \Drupal\fsa_subpages\EventSubscriber\FsaSubpagesRedirectSubscriber
 */

namespace Drupal\fsa_subpages\EventSubscriber;

use Drupal\Core\Url;
use Drupal\node\Entity\Node;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class FsaSubpagesRedirectSubscriber implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return([
      KernelEvents::REQUEST => [
        ['redirectSubpagesNode'],
      ]
    ]);
  }

  /**
   * Redirect requests for node with Sub-pages to first subpage.
   *
   * @param GetResponseEvent $event
   * @return void
   */
  public function redirectSubpagesNode(GetResponseEvent $event) {

    $request = $event->getRequest();

    // dont redirect on non-node-view pages
    $route = \Drupal::routeMatch()->getRouteName();
    if ($route != 'entity.node.canonical') {
      return;
    }

    // make sure we have the node
    $node = \Drupal::routeMatch()->getParameter('node');
    if (empty($node)) {
      // cannot happen.
      // what should be done in this case?
      // fallback: let system handle the situation
      return;
    }

    $nid = $node->id();
    // Reload the node to be sure it is correct type
    $node = Node::load($nid);
    $paragraphs = $node->get('field_subpages')->referencedEntities();
    if (empty($paragraphs)) {
      return;
    }

    // if user is already on valid subpage, dont redirect
    $param = \Drupal::request()->query->all();
    if (!empty($param['subpage'])) {
      $subpage = $param['subpage'];
      if (is_numeric($subpage) && $subpage == (int) $subpage) {
        $subpage = (int) $subpage;
        $count = count($paragraphs);
        if ( 1 <= $subpage && $subpage <= $count) {
          return;
        }
      }
    }

    // do the redirect
    $route = 'entity.node.canonical';
    $params = ['node' => $nid];
    $options = ['query' => ['subpage' => 1]];
    $url = Url::fromRoute($route, $params, $options);
    $url = $url->toString();
    $response = new RedirectResponse($url, 301);
    $event->setResponse($response);
  }

}
