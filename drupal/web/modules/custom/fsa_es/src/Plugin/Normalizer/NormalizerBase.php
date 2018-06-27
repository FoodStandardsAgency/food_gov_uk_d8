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
   * @param \Drupal\fsa_es\Plugin\Normalizer\NodeInterface $object
   *   Node to normalize.
   * @param mixed $format
   *   Format options.
   * @param array $context
   *   Context data.
   *
   * @return array|bool|float|int|string
   *   Normalized node entity.
   */
  public function normalize(NodeInterface $object, $format = NULL, array $context = []) {
    return [
      'entity_type' => $object->getEntityTypeId(),
    ];
  }

}
