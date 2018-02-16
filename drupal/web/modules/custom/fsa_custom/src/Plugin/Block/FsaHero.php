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
    $node = $route->getParameter('node');

    // Add hero's on node content.
    if (is_object($node)) {

      // Most help and webform nodes should have the contact hero.
      if (in_array($node->getType(), ['help', 'webform'])) {
        // And query if the node is set to help menu, on true set the content.
        $query = \Drupal::entityQuery('menu_link_content')
          ->condition('link.uri', 'entity:node/' . $node->id())
          ->condition('menu_name', 'help');
        $result = $query->execute();
        if ((!empty($result)) ? reset($result) : FALSE) {
          $build['fsa_hero'] = [
            '#theme' => $theme,
            '#title' => $this->t('Contact us'),
            '#copy' => ['#markup' => $this->t('Report a food problem, give us feedback or find our contact details.')],
          ];
        }
      }
    }

    // Static hero content for all hygiene rating related pages.
    if ($route->getParameter('fsa_establishment')) {
      $fsa_ratings_config = \Drupal::config('config.fsa_ratings');
      $build['fsa_hero'] = [
        '#theme' => $theme,
        '#title' => $this->t('Food hygiene ratings'),
        '#copy' => check_markup($fsa_ratings_config->get('hero_copy'), 'basic_html'),
      ];
    }

    return $build;
  }

}
