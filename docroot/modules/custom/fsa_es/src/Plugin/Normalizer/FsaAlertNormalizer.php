<?php

namespace Drupal\fsa_es\Plugin\Normalizer;

use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Normalizes Drupal entities into an array structure good for ES.
 */
class FsaAlertNormalizer extends NormalizerBase {

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
   * @param object $data
   *   Entity data.
   * @param mixed $format
   *   Format options.
   *
   * @return bool
   *   Whether object comparison is true or false.
   */
  public function supportsNormalization($data, $format = NULL) {
    return is_a($data, '\Drupal\node\Entity\Node') && $data->bundle() == 'alert';
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

    // Get alert type field.
    $type_field = $object->get('field_alert_type');

    $data = [
      // See comments on the mapping in the index plugin fore news content type.
      'news_type' => $this->getNewsType($type_field->value),
      'name' => $object->label(),
      'body' => implode(' ', [
        $this->prepareTextualField($object->get('field_alert_actiontaken')->value),
        $this->prepareTextualField($object->get('field_alert_description')->value),
      ]),
      'nation' => array_map(function ($item) {
        return [
          'id' => $item->id(),
          'label' => $item->label(),
        ];
      }, $object->get('field_nation')->referencedEntities()),
      'created' => $entity_dates['created'],
      'updated' => $entity_dates['changed'],
    ] + $parent_data;

    return $data;
  }

  /**
   * Returns new type depending on alert type field value.
   *
   * @param string $value
   *   Specified value.
   *
   * @return array|null
   *   Results or null if no matches.
   */
  protected function getNewsType($value) {
    $types = [
      'AA' => $this->t('Allergy alert'),
      'FAFA' => $this->t('Food alert'),
      'PRIN' => $this->t('Food alert'),
    ];

    return isset($types[$value]) ? $types[$value] : NULL;
  }

}
