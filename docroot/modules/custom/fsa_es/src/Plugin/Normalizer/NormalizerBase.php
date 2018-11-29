<?php

namespace Drupal\fsa_es\Plugin\Normalizer;

use Drupal\serialization\Normalizer\ContentEntityNormalizer;

/**
 * Class NormalizerBase.
 */
class NormalizerBase extends ContentEntityNormalizer {

  /**
   * Prepares textual field (strips tags, removes newlines).
   *
   * @param string $string
   *   Text to process.
   * @param string $allowed_tags
   *   Whitelist of permitted tags.
   *
   * @return string
   *   Sanitised text string.
   */
  public function prepareTextualField($string, $allowed_tags = '') {
    $string = strip_tags($string, $allowed_tags);
    $string = str_replace(['&nbsp;', '&amp;'], [' ', ' '], $string);
    $string = trim(preg_replace("/[\r\n]{2,}/", " ", $string));

    return $string;
  }

  /**
   * {@inheritdoc}
   */
  public function normalize($object, $format = NULL, array $context = []) {
    return [
      'entity_type' => $object->getEntityTypeId(),
    ];
  }

}
