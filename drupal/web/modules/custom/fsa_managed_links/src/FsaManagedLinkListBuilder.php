<?php

namespace Drupal\fsa_managed_links;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Link;

/**
 * Defines a class to build a listing of FSA managed link entities.
 *
 * @ingroup fsa_managed_links
 */
class FsaManagedLinkListBuilder extends EntityListBuilder {


  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('FSA managed link ID');
    $header['name'] = $this->t('Name');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var $entity \Drupal\fsa_managed_links\Entity\FsaManagedLink */
    $row['id'] = $entity->id();
    $row['name'] = Link::createFromRoute(
      $entity->label(),
      'entity.fsa_managed_link.edit_form',
      ['fsa_managed_link' => $entity->id()]
    );
    return $row + parent::buildRow($entity);
  }

}
