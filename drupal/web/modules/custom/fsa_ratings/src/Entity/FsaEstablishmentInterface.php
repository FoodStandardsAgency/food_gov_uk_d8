<?php

namespace Drupal\fsa_ratings\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining FSA Establishment entities.
 *
 * @ingroup fsa_ratings
 */
interface FsaEstablishmentInterface extends ContentEntityInterface, EntityChangedInterface, EntityOwnerInterface {

  /**
   * Gets the FSA Establishment name.
   *
   * @return string
   *   Name of the FSA Establishment.
   */
  public function getName();

  /**
   * Sets the FSA Establishment name.
   *
   * @param string $name
   *   The FSA Establishment name.
   *
   * @return \Drupal\fsa_ratings\Entity\FsaEstablishmentInterface
   *   The called FSA Establishment entity.
   */
  public function setName($name);

  /**
   * Gets the FSA Establishment creation timestamp.
   *
   * @return int
   *   Creation timestamp of the FSA Establishment.
   */
  public function getCreatedTime();

  /**
   * Sets the FSA Establishment creation timestamp.
   *
   * @param int $timestamp
   *   The FSA Establishment creation timestamp.
   *
   * @return \Drupal\fsa_ratings\Entity\FsaEstablishmentInterface
   *   The called FSA Establishment entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Returns the FSA Establishment published status indicator.
   *
   * Unpublished FSA Establishment are only visible to restricted users.
   *
   * @return bool
   *   TRUE if the FSA Establishment is published.
   */
  public function isPublished();

  /**
   * Sets the published status of a FSA Establishment.
   *
   * @param bool $published
   *   TRUE to set this FSA Establishment published, FALSE to set it
   *   unpublished.
   *
   * @return \Drupal\fsa_ratings\Entity\FsaEstablishmentInterface
   *   The called FSA Establishment entity.
   */
  public function setPublished($published);

}
