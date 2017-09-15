<?php

namespace Drupal\fsa_signin\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Url;
use Drupal\Core\Link;

/**
 * Provides a 'MyProfileBlock' block.
 *
 * @Block(
 *  id = "my_profile_block",
 *  admin_label = @Translation("My profile"),
 * )
 */
class MyProfileBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];

    // Disable block cache
    $build['#cache'] = ['max-age' => 0];

    $markup  = '<ul>';
    if (\Drupal::currentUser()->isAuthenticated()) {
      $markup .= self::createLinkMarkup('fsa_signin.default_controller_emailSubscriptionsPage', 'Email subscriptions', '<li>', '</li>');
      $markup .= self::createLinkMarkup('fsa_signin.default_controller_smsSubscriptionsPage', 'SMS subscriptions', '<li>', '</li>');
      $markup .= self::createLinkMarkup('fsa_signin.default_controller_myAccountPage', 'My account', '<li>', '</li>');
      $markup .= self::createLinkMarkup('user.logout.http', 'Logout', '<li>', '</li>');
    }
    else {
      $markup .= self::createLinkMarkup('user.login.http', 'Sign in', '<li>', '</li>');
    }
    $markup .= '</ul>';

    $build['my_profile_block']['#markup'] = $markup;
    return $build;
  }

  protected static function createLinkMarkup($route_name, $text, $prefix = '', $suffix = '') {
    $link_object = Link::createFromRoute($text, $route_name);
    return $prefix . $link_object->toString() . $suffix;
  }
}
