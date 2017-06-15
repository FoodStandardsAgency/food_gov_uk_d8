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
  public function buildHeader() {
    $header['id'] = $this->t('FSA Authority ID');
    $header['name'] = $this->t('Local authority name');
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
        'entity.fsa_authority.edit_form', [
          'fsa_authority' => $entity->id(),
        ]
      )
    );
    return $row + parent::buildRow($entity);
  }

}
