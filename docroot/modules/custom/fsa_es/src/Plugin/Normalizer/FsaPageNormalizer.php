<?php

namespace Drupal\fsa_es\Plugin\Normalizer;

use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Normalizes Drupal entities into an array structure good for ES.
 */
class FsaPageNormalizer extends NormalizerBase {

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

    // Get audience term tree indexed by term ID.
    $audience_term_tree = $this->getTaxonomyTree('audience');

    // Updated value is going to be either from "field_update_date" or from
    // "changed" field.
    $date_updated = $object->get('field_update_date')->value;

    // Get dates.
    $entity_dates = [];
    foreach (['created', 'changed'] as $date_field) {
      $entity_dates[$date_field] = $this->dateFormatter->format($object->get($date_field)->value, 'custom', DATETIME_DATETIME_STORAGE_FORMAT, DATETIME_STORAGE_TIMEZONE);
    }

    $data = [
      'name' => $object->label(),
      'intro' => $this->prepareTextualField($object->get('field_intro')->getString()),
      'body' => $this->prepareTextualField($object->get('body')->getString()),
      'content_type' => array_map(function ($item) {
        return [
          'id' => $item->id(),
          'label' => $item->label(),
        ];
      }, $object->get('field_content_type')->referencedEntities()),
      'audience' => array_map(function ($item) use ($audience_term_tree) {
        return [
          'id' => $item->id(),
          'depth' => isset($audience_term_tree[$item->id()]->depth) ? $audience_term_tree[$item->id()]->depth : 0,
          'label' => $item->label(),
        ];
      }, $object->get('field_audience')->referencedEntities()),
      'nation' => array_map(function ($item) {
        return [
          'id' => $item->id(),
          'label' => $item->label(),
        ];
      }, $object->get('field_nation')->referencedEntities()),
      'created' => $entity_dates['created'],
      'updated' => $date_updated ? $date_updated : $entity_dates['changed'],
    ] + $parent_data;

    return $data;
  }

  /**
   * Returns a taxonomy tree indexed by term IDs.
   *
   * @param string $vid
   *   Vocabulary machine name.
   *
   * @return object[]
   *   Array of term objects.
   */
  protected function getTaxonomyTree(string $vid) {
    if (!isset($this->taxonomyTreeCache[$vid])) {
      if ($tree = $this->entityTypeManager->getStorage('taxonomy_term')->loadTree($vid)) {
        // Store terms keyed by term ID.
        $indexed_tree = [];

        foreach ($tree as $term) {
          $indexed_tree[$term->tid] = $term;
        }

        $this->taxonomyTreeCache[$vid] = $indexed_tree;
      }
      else {
        // Store empty result in cache.
        $this->taxonomyTreeCache[$vid] = NULL;
      }
    }

    return !is_null($this->taxonomyTreeCache[$vid]) ? $this->taxonomyTreeCache[$vid] : [];
  }

}
