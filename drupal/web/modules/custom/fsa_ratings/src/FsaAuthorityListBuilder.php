<?php

namespace Drupal\fsa_ratings;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Routing\LinkGeneratorTrait;
use Drupal\Core\Url;

/**
 * Defines a class to build a listing of FSA Authority entities.
 *
 * @ingroup fsa_ratings
 */
class FsaAuthorityListBuilder extends EntityListBuilder {

  use LinkGeneratorTrait;

  /**
   * {@inheritdoc}
   */
  public function load() {

    // Load query for click-sorting of the listing.
    $entity_query = \Drupal::service('entity.query')->get('fsa_authority');
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
    $header['id'] = array(
      'data' => $this->t('ID'),
      'field' => 'id',
      'specifier' => 'id',
      'class' => array(RESPONSIVE_PRIORITY_LOW),
    );

    // Make sort clickable.
    $header['Name'] = array(
      'data' => $this->t('Authority'),
      'field' => 'name',
      'specifier' => 'name',
      'class' => array(RESPONSIVE_PRIORITY_LOW),
    );

    $header['langcode'] = $this->t('Language');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var $entity \Drupal\fsa_ratings\Entity\FsaAuthority */
    $row['id'] = $entity->id();
    $row['name'] = $this->l(
      $entity->label(),
      new Url(
        'entity.fsa_authority.canonical', [
          'fsa_authority' => $entity->id(),
        ]
      )
    );
    $row['langcode'] = $entity->language()->getName();
    return $row + parent::buildRow($entity);
  }

}
