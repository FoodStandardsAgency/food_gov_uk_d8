<?php

namespace Drupal\fsa_alt_mail_update\Controller;

class FsaAltMailUpdate {
  public function render() {
    $path = 'modules/custom/fsa_alt_mail_update/data.csv';
    $file = fopen($path, 'r');
    $flag = TRUE;
    while (($line = fgetcsv($file)) !== FALSE) {
      if ($flag) {
        $flag = FALSE;
        continue;
      }
      $rows[] = $line;
    }
    kint($rows);

    $output[] = array('#markup' => 'abc');
    return $output;
  }
}
