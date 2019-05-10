<?php

namespace Drupal\fsa_es\Plugin\Normalizer;

use Drupal\serialization\Normalizer\ContentEntityNormalizer;

/**
 * Class NormalizerBase.
 */
class NormalizerBase extends ContentEntityNormalizer {

  protected $lang;

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
   * If there's a translated label for the passed entity, return it.
   * Assumes $lang is set before hand so should be called in
   * the normalize() function of sub class.
   *
   * @param $entity
   *   The entity to get the label from.
   *
   * @return string || NULL
   *   The translated string if found.
   */
  public function getTranslatedLabel($entity) {
    if ($entity) {
      return $entity->hasTranslation($this->lang)
        ? $entity->getTranslation($this->lang)->label()
        : $entity->label();
    }
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function normalize($object, $format = NULL, array $context = []) {
    $this->lang = $object->get('langcode')->value;
    return [
      'entity_type' => $object->getEntityTypeId(),
    ];
  }

}
