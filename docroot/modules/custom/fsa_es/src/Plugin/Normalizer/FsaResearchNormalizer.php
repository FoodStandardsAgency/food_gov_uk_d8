<?php

namespace Drupal\fsa_es\Plugin\Normalizer;

use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Normalizes Drupal entities into an array structure good for ES.
 */
class FsaResearchNormalizer extends NormalizerBase {

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
   * FsaPageNormalizer constructor.
   *
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   Entity manager interface.
   * @param \Drupal\Core\Datetime\DateFormatterInterface $date_formatter
   *   Date formatter interface.
   */
  public function __construct(EntityManagerInterface $entity_manager, DateFormatterInterface $date_formatter) {
    parent::__construct($entity_manager);
    $this->dateFormatter = $date_formatter;
  }

  /**
   * {@inheritdoc}
   */
  public function supportsNormalization($data, $format = NULL) {
    return is_a($data, '\Drupal\node\Entity\Node') && $data->bundle() == 'research_project';
  }

  /**
   * {@inheritdoc}
   */
  public function normalize($object, $format = NULL, array $context = []) {
    $parent_data = parent::normalize($object, $format, $context);

    // Updated value is going to be either from "field_update_date" or from
    // "changed" field.
    $date_updated = $object->get('field_update_date')->value;

    // Get dates.
    $entity_dates = [];
    foreach (['created', 'changed'] as $date_field) {
      $entity_dates[$date_field] = $this->dateFormatter->format($object->get($date_field)->value, 'custom', DATETIME_DATETIME_STORAGE_FORMAT, DATETIME_STORAGE_TIMEZONE);
    }

    $data = [
      'name' => $this->getTranslatedLabel($object),
      'project_code' => $object->get('field_research_project_code')->value,
      'body' => implode(' ', [
        $this->prepareTextualField($object->get('field_intro')->value),
        $this->prepareTextualField($object->get('body')->value),
      ]),
      'topics' => array_map(function ($item) {
        return [
          'id' => $item->id(),
          'label' => $this->getTranslatedLabel($item),
        ];
      }, $object->get('field_research_topics')->referencedEntities()),
      'nation' => array_map(function ($item) {
        return [
          'id' => $item->id(),
          'label' => $this->getTranslatedLabel($item),
        ];
      }, $object->get('field_nation')->referencedEntities()),
      'created' => $entity_dates['created'],
      'updated' => $date_updated ? $date_updated : $entity_dates['changed'],
    ] + $parent_data;

    return $data;
  }

}
