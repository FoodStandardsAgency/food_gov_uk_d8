<?php
/**
 * @file
 * Contains Drupal\fsa_ratings_import\Plugin\migrate\process\RatingValue.
 */

namespace Drupal\fsa_ratings_import\Plugin\migrate\process;

use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;

/**
 * Prepends the authority core with 1 to avoid losing the leading zero's.
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
    var_dump($value);
    $value = 1 . $value;
    return $value;
  }

}
