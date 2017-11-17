<?php

namespace Drupal\fsa_es\Plugin\Normalizer;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityManager;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\file\Entity\File;
use Drupal\image\Entity\ImageStyle;
use Drupal\serialization\Normalizer\ContentEntityNormalizer;

/**
 * Normalizes / denormalizes Drupal entities into an array structure good for ES.
 */
class FsaRatingsNormalizer extends ContentEntityNormalizer {

  use StringTranslationTrait;

  /**
   * The interface or class that this Normalizer supports.
   *
   * @var array
   */
  protected $supportedInterfaceOrClass = ['Drupal\Core\Entity\ContentEntityBase'];

  /**
   * Supported formats.
   *
   * @var array
   */
  protected $format = ['elasticsearch_helper'];

  /**
   * FsaRatingsNormalizer constructor.
   *
   * @param \Drupal\Core\Entity\EntityManager $entityManager
   */
  public function __construct(EntityManager $entityManager) {
    parent::__construct($entityManager);
  }

  /**
   * {@inheritdoc}
   */
  public function normalize($object, $format = NULL, array $context = []) {
    // This normalizer supports only ContentEntityBase objects, see $this->supportedInterfaceOrClass.
    /** @var \Drupal\Core\Entity\ContentEntityBase $object */
    $type = $object->getEntityTypeId();

    switch ($type) {
      case 'node':
        return NULL;

      case 'fsa_establishment':
        return $this->normalizeEstablishment($object, $format, $context);

      default:
        return $this->normalizeDefault($object, $format, $context);
    }

  }

  /**
   * Normalize generic entity attributes.
   *
   * @param \Drupal\Core\Entity\ContentEntityBase $object
   * @param null $format
   * @param array $context
   *
   * @return array
   */
  protected function normalizeDefault(ContentEntityBase $object, $format = NULL, array $context = []) {
    $data = [
      'id' => $object->id(),
      'name' => $object->get('name')->getString(),
    ];

    return $data;
  }

  /**
   * Normalize the 'fsa_establishment' entity specific fields.
   *
   * @param \Drupal\Core\Entity\ContentEntityBase $object
   * @param null $format
   * @param array $context
   *
   * @return array
   */
  protected function normalizeEstablishment(ContentEntityBase $object, $format = NULL, array $context = []) {
    $data = $this->normalizeDefault($object, $format, $context);

    $field_names = [
      'address',
      'businesstype',
      'geocode',
      'localauthoritycode',
      'newratingpending',
      'phone',
      'postcode',
      'ratingdate',
      'ratingvalue',
      'fhis_ratingvalue',
      'fhrs_ratingvalue',
      'righttoreply',
      'schemetype',
      'score_confidence',
      'score_hygiene',
      'score_structural',
    ];

    foreach ($field_names as $field_name) {
      $value = $this->getFieldValue($object, $field_name);
      if ($field_name == 'businesstype') {
        $langcode = $object->language()->getId();
        // Businesstype value needs translation since the establishment import doesn't bring in translation at the moment due technical issues.
        $value[0]['label'] = $this->t($value[0]['label'], [], ['context' => 'FHRS business type', 'langcode' => $langcode])->render();
      }
      $data[$field_name] = $value;
    }

    // Merge the values of values from name, address, postcode and LA into single field for more robust search querying.
    $data['combinedvalues'] = $data['name'] . ' ' . $data['address'] . ' ' . $data['postcode'] . ' ' . $data['localauthoritycode'][0]['label'];

    // Merge the values of values from name and postcode.
    $data['combined_name_postcode'] = $data['name'] . ' ' . $data['postcode'];

    // Merge the values of values from name and location.
    $data['combined_name_location'] = $data['name'] . ' ' . $data['localauthoritycode'][0]['label'];

    // Merge the values of values from location and postcode.
    $data['combined_location_postcode'] = $data['localauthoritycode'][0]['label'] . ' ' . $data['postcode'];

    return $data;
  }

  /**
   * Helper method to retrieve field values with as flat structure as possible.
   *
   * @param \Drupal\Core\Entity\ContentEntityBase $content_entity
   *   Fully loaded content entity (Node, FieldCollectionItem etc).
   * @param string $field_name
   *   Field name with or without the 'field_' in the beginning.
   *
   * @return mixed
   *   Value of the given field.
   *
   * @TODO: move this into a service class
   */
  protected function getFieldValue($content_entity, $field_name) {
    $ret = NULL;

    if (strpos($field_name, 'field_') !== 0 && strpos($field_name, 'user_') !== 0) {
      $field_name = 'field_' . $field_name;
    }

    if ($content_entity->hasField($field_name)) {
      $field_type = $content_entity->getFieldDefinition($field_name)->getType();

      switch ($field_type) {
        case 'datetime':
        case 'boolean':
        case 'string_long':
        case 'string':
        case 'integer':
        case 'decimal':
        case 'email':
        case 'telephone':
        case 'link':
        case 'list_integer':
          $ret = trim($content_entity->get($field_name)->getString());
          break;

        case 'list_string':
          $store_plain = [
            'field_schemetype',
            'field_fhis_ratingvalue',
            'field_fhrs_ratingvalue',
          ];
          if (in_array($field_name, $store_plain)) {
            $ret = trim($content_entity->get($field_name)->getString());
          }
          else {
            $ret = [];
            foreach ($content_entity->{$field_name} as $ref) {
              $ret[] = trim($ref->getString());
            }
          }

          break;

        case 'entity_reference':
          $ret = [];
          foreach ($content_entity->{$field_name} as $ref) {
            if (isset($ref->entity)) {
              $ret[] = [
                'id' => $ref->entity->id(),
                'label' => $ref->entity->label(),
              ];
            }
          }
          break;

        case 'image':
          $img_arr = $content_entity->get($field_name)->getValue();
          $ret = [];
          foreach ($img_arr as $img) {
            $file_uri = File::load($img['target_id'])->getFileUri();
            $ret[] = [
              'original' => file_create_url($file_uri),
              'formatted_small' => ImageStyle::load('thumbnail')->buildUrl($file_uri),
              'formatted_medium' => ImageStyle::load('medium')->buildUrl($file_uri),
              'formatted_large' => ImageStyle::load('large')->buildUrl($file_uri),
            ];
          }
          break;

        case 'field_collection':
          $value_arr = $content_entity->get($field_name)->getValue();
          $ret = [];
          foreach ($value_arr as $val) {
            /** @var \Drupal\Core\Entity\ContentEntityBase $collection_item */
            $collection_item = \Drupal::entityTypeManager()
              ->getStorage('field_collection_item')
              ->loadRevision($val['revision_id']);
            if (empty($collection_item)) {
              continue;
            }

            $item_values = $collection_item->toArray();
            $fields = [];
            foreach ($item_values as $key => $item_val) {
              if (strpos($key, 'field_') === 0) {
                $field_label = str_replace('field_', '', $key);
                $fields[$field_label] = $this->getFieldValue($collection_item, $key);
              }
            }
            // Name attribute is unneeded meta data.
            unset($fields['name']);

            $ret[] = array_merge(
              ['id' => $collection_item->id(), 'revision_id' => $val['revision_id']],
              $fields
            );
          }
          break;

        case 'geolocation':
          $ret = [];
          $geoloc_value = $content_entity->get($field_name)->getValue();
          if (!empty($geoloc_value)) {
            $ret['lat'] = $geoloc_value[0]['lat'];
            $ret['lon'] = $geoloc_value[0]['lng'];
          }
          break;

        default:
          $ret = "***$field_type***";
      }
    }
    return $ret;
  }

}
