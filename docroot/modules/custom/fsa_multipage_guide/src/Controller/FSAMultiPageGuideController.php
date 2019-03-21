<?php

namespace Drupal\fsa_multipage_guide\Controller;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Controller\ControllerBase;
use Drupal\fsa_multipage_guide\FSAMultiPageGuide;
use Symfony\Component\HttpFoundation\RedirectResponse;

class FSAMultiPageGuideController extends ControllerBase {


  /**
   * Restrict access to the manage guide tab for nodes.
   *
   * @param $node
   *
   * @return \Drupal\Core\Access\AccessResultAllowed|\Drupal\Core\Access\AccessResultForbidden
   */
  public function manageAccess($node) {
    $page = \Drupal\node\Entity\Node::load($node);

    if (FSAMultiPageGuide::IsPage($page)) {
      return AccessResult::allowed();
    }

    return AccessResult::forbidden();
  }

  /**
   * Send the user off to make a new guide or edit the existing one for the page.
   */
  public function manage() {
    $lang_code = \Drupal::languageManager()->getCurrentLanguage()->getId();
    $node_id = \Drupal::routeMatch()->getParameter('node');

    $page = \Drupal\node\Entity\Node::load($node_id);
    $guide = FSAMultiPageGuide::GetGuideForPage($page);

    $redirect_url = '/node/add/multipage_guide';

    if (empty($guide)) {
      \Drupal::messenger()->addMessage(t('%page is not currently part of a guide, you will need to create one using the form below and add it too it.', [
        '%page' => $page->getTitle(),
      ]));
    }
    else {
      $redirect_url = '/node/' . $guide->getId() . '/edit';
      if ($lang_code !== 'en') {
        $redirect_url = '/' . $lang_code . $redirect_url;
      }
    }

    return new RedirectResponse($redirect_url);
  }

}
