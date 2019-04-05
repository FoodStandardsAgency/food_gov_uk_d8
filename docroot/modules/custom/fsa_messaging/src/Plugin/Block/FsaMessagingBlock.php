<?php

namespace Drupal\fsa_messaging\Plugin\Block;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Provides a block to show a site wide message.
 *
 * @Block(
 *   id = "fsa_messaging_block",
 *   admin_label = @Translation("FSA Messaging Block"),
 * )
 */
class FsaMessagingBlock extends BlockBase {
  /**
   * {@inheritdoc}
   */
  public function build() {

    $language = \Drupal::languageManager()->getCurrentLanguage()->getId();
    $default_language = \Drupal::languageManager()->getDefaultLanguage()->getId();

    // If we're not looking at the default language, the ajax path needs prefixed.
    $language = $language === $default_language ? '' : $language . '/';

    return array(
      'block_wrapper' => array(
        '#prefix' => '<span class="' . $language . ' ' . $default_language .'"></span><div id="fsa-messaging-block-wrapper">',
        '#suffix' => '</div>',
        '#markup' => '',
      ),
      '#attached' => array(
        'library' => array('fsa_messaging/fsa_messaging'),
        'drupalSettings' => array('langPrefix' => $language)
      )
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function blockAccess(AccountInterface $account) {
    return AccessResult::allowedIfHasPermission($account, 'access content');
  }

}
