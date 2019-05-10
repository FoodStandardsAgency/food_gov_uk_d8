<?php

namespace Drupal\fsa_es\Plugin\Normalizer;

use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Normalizes Drupal entities into an array structure good for ES.
 */
class FsaConsultationNormalizer extends NormalizerBase {

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
   *   Entity Manager interface.
   * @param \Drupal\Core\Datetime\DateFormatterInterface $date_formatter
   *   Date Formatter interface.
   */
  public function __construct(EntityManagerInterface $entity_manager, DateFormatterInterface $date_formatter) {
    parent::__construct($entity_manager);
    $this->dateFormatter = $date_formatter;
  }

  /**
   * @param object $data
   *   Entity data.
   * @param mixed $format
   *   Formatting data.
   *
   * @return bool
   *   Whether or not object types match.
   */
  public function supportsNormalization($data, $format = NULL) {
    return is_a($data, '\Drupal\node\Entity\Node') && $data->bundle() == 'consultation';
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

    // Get consultations type field.
    $type_field = $object->get('field_consultations_type');

    // Get dates.
    $consultation_start_date = $object->get('field_consultation_launch_date')->first();
    $consultation_close_date = $object->get('field_consultation_closing_date')->first();

    $data = [
      // See comments on the mapping in the index plugin for news content type.
      'news_type' => $this->getTranslatedLabel($type_field->entity),
      'status' => (bool) $object->get('field_status')->value,
      'responses_published' => ($object->get('field_consultation_summary')->count() > 0),
      'consultation_start_date' => $consultation_start_date ? $consultation_start_date->date->format(DATETIME_DATETIME_STORAGE_FORMAT) : NULL,
      'consultation_close_date' => $consultation_close_date ? $consultation_close_date->date->format(DATETIME_DATETIME_STORAGE_FORMAT) : NULL,
      'name' => $this->getTranslatedLabel($object),
      'body' => implode(' ', [
        $this->prepareTextualField($object->get('field_intro')->value),
        $this->prepareTextualField($object->get('body')->value),
      ]),
      'nation' => array_map(function ($item) {
        return [
          'id' => $item->id(),
          'label' => $this->getTranslatedLabel($item),
        ];
      }, $object->get('field_nation')->referencedEntities()),
      'created' => $entity_dates['created'],
      'updated' => $entity_dates['changed'],
    ] + $parent_data;

    return $data;
  }

}
