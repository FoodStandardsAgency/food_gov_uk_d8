<?php

namespace Drupal\fsa_custom;

/**
 * @file
 * Contains \Drupal\fsa_custom\FsaCustomHelper.
 */

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Link;

/**
 * Controller class for the FSA custom module.
 *
 * @package Drupal\fsa_custom\Controller
 */
class FsaCustomHelper extends ControllerBase {

  /**
   * Privacy notice links.
   *
   * Site is linking to the privacy notice documents in various places to. This
   * helper function serves the correct link around our custom code.
   *
   * @param string $type
   *   Type of the link (rafp | ncfu).
   *
   * @return \Drupal\Core\GeneratedLink
   *   Link object.
   */
  public static function privacyNoticeLink($type) {

    // @todo: Make the document target configurable.
    switch ($type) {
      case 'rafp':
        $ent_id = 607;
        break;

      case 'ncfu':
        $ent_id = 1069;
        break;

      default:
        $ent_id = FALSE;
    }

    if ($ent_id) {
      $link = Link::createFromRoute(
        t('Privacy notice') . ' ' . $type,
        'entity.media.canonical',
        ['media' => $ent_id],
        [])->toString();
    }
    else {
      $link = [];
    }

    return $link;
  }

}
