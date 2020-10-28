<?php

namespace Drupal\fsa_es\Plugin\Normalizer;

use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Normalizes Drupal entities into an array structure good for ES.
 */
class FsaEvidenceNormalizer extends NormalizerBase {

  use StringTranslationTrait;

  /**
   * @var array
   */
  protected $taxonomyTreeCache = [];

  /**
   * The interface or class that this Normalizer supports.
   *
   * @var array
   */
  protected $supportedInterfaceOrClass = ['\Drupal\node\Entity\Node'];

  /**
   * Supported formats.
   *
   * @var array
   */
  protected $format = ['elasticsearch_helper'];

  /**
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * FsaEvidenceNormalizer constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity manager interface.
   * @param \Drupal\Core\Datetime\DateFormatterInterface $date_formatter
   *   Date formatter interface.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, DateFormatterInterface $date_formatter) {
    parent::__construct($entity_type_manager);
    $this->dateFormatter = $date_formatter;
  }

  /**
   * {@inheritdoc}
   */
  public function supportsNormalization($data, $format = NULL) {
    return is_a($data, '\Drupal\node\Entity\Node') && $data->bundle() == 'page';
  }

  /**
   * {@inheritdoc}
   */
  public function normalize($object, $format = NULL, array $context = []) {
    $parent_data = parent::normalize($object, $format, $context);

    // Get dates.
    $entity_dates = [];
    foreach (['created', 'changed'] as $date_field) {
      $entity_dates[$date_field] = $this->dateFormatter->format($object->get($date_field)->value, 'custom', DATETIME_DATETIME_STORAGE_FORMAT, DATETIME_STORAGE_TIMEZONE);
    }

    $data = [
      'name' => $this->getTranslatedLabel($object),
      'intro' => $this->prepareTextualField($object->get('field_intro')->getString()),
      'body' => $this->prepareTextualField($object->get('body')->getString()),
      'nation' => array_map(function ($item) {
        return [
          'id' => $item->id(),
          'label' => $this->getTranslatedLabel($item),
        ];
      }, $object->get('field_nation')->referencedEntities()),
      'topic' => array_map(function ($item) {
        return [
          'id' => $item->id(),
          'label' => $this->getTranslatedLabel($item),
        ];
      }, $object->get('field_research_topics')->referencedEntities()),
      'type' => array_map(function ($item) {
        return [
          'id' => $item->id(),
          'label' => $this->getTranslatedLabel($item),
        ];
      }, $object->get('field_evidence_type')->referencedEntities()),
      'created' => $entity_dates['created'],
      'updated' => $entity_dates['changed'],
    ] + $parent_data;

    return $data;
  }

}
