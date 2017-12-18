<?php

namespace Drupal\fsa_es\Plugin\Normalizer;

use Drupal\serialization\Normalizer\ContentEntityNormalizer;

/**
 * Class NormalizerBase
 */
class NormalizerBase extends ContentEntityNormalizer {

  /**
   * Prepares textual field (strips tags, removes newlines).
   *
   * @param $string
   * @param $allowed_tags
   *
   * @return string
   */
  public function prepareTextualField($string, $allowed_tags = '') {
    $string = strip_tags($string, $allowed_tags);
    $string = str_replace(['&nbsp;', '&amp;'], [' ', ' '], $string);
    $string = trim(preg_replace("/[\r\n]{2,}/", " ", $string));

    return $string;
  }

}
