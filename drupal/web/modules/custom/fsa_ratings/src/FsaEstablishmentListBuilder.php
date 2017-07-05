<?php

namespace Drupal\fsa_ratings;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Routing\LinkGeneratorTrait;
use Drupal\Core\Url;

/**
 * Defines a class to build a listing of FSA Establishment entities.
 *
 * @ingroup fsa_ratings
 */
class FsaEstablishmentListBuilder extends EntityListBuilder {

  use LinkGeneratorTrait;

  /**
   * {@inheritdoc}
   */
  public function load() {

    // Load query for click-sorting of the listing.
    $entity_query = \Drupal::service('entity.query')->get('fsa_establishment');
    $header = $this->buildHeader();

    // Generic default of 50 items in page.
    $entity_query->pager(50);
    $entity_query->tableSort($header);

    $entities = $entity_query->execute();

    return $this->storage->loadMultiple($entities);
  }

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {

    // Make sort clickable.
    $header['id'] = [
      'data' => $this->t('ID'),
      'field' => 'id',
      'specifier' => 'id',
      'class' => [RESPONSIVE_PRIORITY_LOW],
    ];

    // Make sort clickable.
    $header['Name'] = [
      'data' => $this->t('Establishment'),
      'field' => 'name',
      'specifier' => 'name',
      'class' => [RESPONSIVE_PRIORITY_LOW],
    ];

    $header['langcode'] = $this->t('Language');

    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var $entity \Drupal\fsa_ratings\Entity\FsaEstablishment */
    $row['id'] = $entity->id();
    $row['name'] = $this->l(
      $entity->label(),
      new Url(
        'entity.fsa_establishment.canonical', [
          'fsa_establishment' => $entity->id(),
        ]
      )
    );
    $row['langcode'] = $entity->language()->getName();
    return $row + parent::buildRow($entity);
  }

}
