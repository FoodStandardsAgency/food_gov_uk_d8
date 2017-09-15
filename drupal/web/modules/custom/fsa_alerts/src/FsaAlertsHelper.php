<?php

namespace Drupal\fsa_alerts;

use Drupal\Core\Link;

/**
 * @file
 * Contains \Drupal\fsa_alerts\FsaAlertsHelper.
 */

/**
 * Alert helpers controller.
 */
class FsaAlertsHelper {

  /**
   * CTA link to Alerts subscribe page.
   *
   * @return object
   *   Link object.
   */
  public static function ctaSubscribe() {

    $text = t('Subscribe to news & alerts');
    $options = ['attributes' => ['class' => 'button']];

    try {
      $cta = Link::createFromRoute($text, 'fsa_signin.user_preregistration_alerts_form', [], $options);
    }
    catch (\Exception $e) {
      // In case the link creation when route was non-existent
      $cta = Link::createFromRoute($text, 'user.register', [], $options);
    }

    return $cta;

  }

}
