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

      case 'nfcu':
        $ent_id = 1069;
        break;

      case 'alerts':
        $ent_id = 919;
        $entity_type = 'node';
        break;

      default:
        $ent_id = FALSE;
    }

    if ($ent_id) {
      $entity_type = !empty($entity_type) ? $entity_type : 'media';
      $link = Link::createFromRoute(
        t('Privacy notice'),
        "entity.{$entity_type}.canonical",
        [$entity_type => $ent_id],
        [
          'attributes' => [
            'class' => 'privacy-link ext',
            'target' => '_blank',
          ],
        ])->toString();
    }
    else {
      $link = '<pre>[privacy link not defined]</pre>';
    }

    return $link;
  }

}
