<?php

namespace Drupal\fsa_custom\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a 'FsaHero' block.
 *
 * The "FSA Hero" block is set to display on all pages, contextually send
 * variables here in the build() function. If $build is empty the block is not
 * displayed at all.
 *
 * @Block(
 *  id = "fsa_hero",
 *  admin_label = @Translation("FSA static/contextual hero"),
 * )
 */
class FsaHero extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $theme = 'fsa_hero';
    $build = [];
    $route = \Drupal::routeMatch();
    $route_name = $route->getRouteName();

    // Example to display something on a help CT.
    /*
     *   // Get node object and continue only if it exists.
     *   $node = $route->getParameter('node');
     *   if (is_object($node)) {
     *     if ($node->getType() == 'help') {
     *       $build['fsa_hero'] = [
     *         '#theme' => $theme,
     *         '#attributes' => ['class' => ['extraclass']],
     *         '#title' => $this->t('Contact us'),
     *         '#copy' => $this->t('Example copy text'),
     *       ];
     *     }
     *   }
     */

    // Static hero content for all hygiene rating related pages.
    if ($route_name == 'fsa_ratings.ratings_search' ||
      $route_name == 'fsa_ratings.ratings_meanings' ||
      $route->getParameter('fsa_establishment') ||
      $route->getParameter('fsa_authority')) {
      $build['fsa_hero'] = [
        '#theme' => $theme,
        '#title' => $this->t('Food hygiene ratings'),
      ];
    }

    return $build;
  }

}
