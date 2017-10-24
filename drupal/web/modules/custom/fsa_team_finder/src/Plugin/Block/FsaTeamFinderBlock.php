<?php

namespace Drupal\fsa_team_finder\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a 'Team finder' Block
 * @Block(
 *   id = "fsa_team_finder_block",
 *   admin_label = @Translation("Team finder"),
 * )
 */

class FsaTeamFinderBlock extends BlockBase {
  /**
   *
   * {@inheritdoc}
   *
   * Block content.
   */
  public function build() {
    return \Drupal::formBuilder()->getForm('Drupal\fsa_team_finder\Form\TeamFinder');
  }
}
