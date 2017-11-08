<?php

namespace Drupal\fsa_ratings_import\Plugin\migrate\process;

use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;

/**
 * Local AuthorityCode transform class.
 *
 * Transform function prepends LocalAuthorityCode with "1" to be sure it stores
 * as integer in order to avoid losing the leading zero's.
 *
 * @MigrateProcessPlugin(
 *   id = "local_authority_code",
 * )
 */
class LocalAuthorityCode extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $value = 1 . $value;
    return $value;
  }

}
