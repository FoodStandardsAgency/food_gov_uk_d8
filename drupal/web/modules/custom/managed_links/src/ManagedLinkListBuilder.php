<?php

namespace Drupal\managed_links;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Link;

/**
 * Defines a class to build a listing of Managed Link entities.
 *
 * @ingroup managed_links
 */
class ManagedLinkListBuilder extends EntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('Managed Link ID');
    $header['name'] = $this->t('Name');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var $entity \Drupal\managed_links\Entity\ManagedLink */
    $row['id'] = $entity->id();
    $row['name'] = Link::createFromRoute(
      $entity->label(),
      'entity.managed_link.edit_form',
      ['managed_link' => $entity->id()]
    );
    return $row + parent::buildRow($entity);
  }

}
