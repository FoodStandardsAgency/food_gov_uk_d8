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

    $markup  = '<ul>';
    $markup .= self::createLinkMarkup('fsa_signin.default_controller_emailSubscriptionsPage', 'Email subscriptions', '<li>', '</li>');
    $markup .= self::createLinkMarkup('fsa_signin.default_controller_smsSubscriptionsPage', 'SMS subscriptions', '<li>', '</li>');
    $markup .= self::createLinkMarkup('fsa_signin.default_controller_myAccountPage', 'My account', '<li>', '</li>');
    $markup .= self::createLinkMarkup('user.logout.http', 'Logout', '<li>', '</li>');
    $markup .= '</ul>';

    $build['my_profile_block']['#markup'] = $markup;

    return $build;
  }

  protected static function createLinkMarkup($route_name, $text, $prefix = '', $suffix = '') {
    $url = Url::fromRoute($route_name);
    $link = Link::fromTextAndUrl(t($text), $url);
    $link = $link->toRenderable();
    $link['#attributes'] = ['class' => ['profile-link']];
    return $prefix . render($link) . $suffix;
  }
}
