<?php

namespace Drupal\fsa_team_finder\Controller;

class FsaTeamFinder {
  public function render() {
    $output[] = array('#markup' => t('<p>Food safety teams advise consumers and businesses on food related issues, such as inspections of food premises, and concerns and complaints raised by the public.</p>'));
    $output[] = \Drupal::formBuilder()->getForm('Drupal\fsa_team_finder\Form\TeamFinder');
    return $output;
  }
}
