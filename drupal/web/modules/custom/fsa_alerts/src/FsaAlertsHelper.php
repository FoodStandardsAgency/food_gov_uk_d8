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

    if (\Drupal::currentUser()->isAuthenticated()) {
      $text = t('Manage your subscription');
      $route = 'fsa_signin.user_preregistration_alerts_form';
    }
    else {
      $text = t('Subscribe to news and alerts');
      $route = 'fsa_signin.default_controller_signInPage';
    }

    $options = ['attributes' => ['class' => 'button']];

    try {
      $cta = Link::createFromRoute($text, $route, [], $options);
    }
    catch (\Exception $e) {
      // In case the link creation when route was non-existent.
      $cta = Link::createFromRoute($text, 'user.register', [], $options);
    }

    return $cta;

  }

}
