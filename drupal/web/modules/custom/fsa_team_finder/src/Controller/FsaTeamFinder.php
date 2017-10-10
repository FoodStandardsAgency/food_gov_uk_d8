<?php

namespace Drupal\fsa_team_finder\Controller;

class FsaTeamFinder {
  public function render() {
    return \Drupal::formBuilder()->getForm('Drupal\fsa_team_finder\Form\TeamFinder');
  }
}
