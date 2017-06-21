<?php

namespace Drupal\fsa_ratings\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining FSA Authority entities.
 *
 * @ingroup fsa_ratings
 */
interface FsaAuthorityInterface extends ContentEntityInterface, EntityChangedInterface, EntityOwnerInterface {

  /**
   * Gets the FSA Authority name.
   *
   * @return string
   *   Name of the FSA Authority.
   */
  public function getName();

  /**
   * Sets the FSA Authority name.
   *
   * @param string $name
   *   The FSA Authority name.
   *
   * @return \Drupal\fsa_ratings\Entity\FsaAuthorityInterface
   *   The called FSA Authority entity.
   */
  public function setName($name);

  /**
   * Gets the FSA Authority creation timestamp.
   *
   * @return int
   *   Creation timestamp of the FSA Authority.
   */
  public function getCreatedTime();

  /**
   * Sets the FSA Authority creation timestamp.
   *
   * @param int $timestamp
   *   The FSA Authority creation timestamp.
   *
   * @return \Drupal\fsa_ratings\Entity\FsaAuthorityInterface
   *   The called FSA Authority entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Returns the FSA Authority published status indicator.
   *
   * Unpublished FSA Authority are only visible to restricted users.
   *
   * @return bool
   *   TRUE if the FSA Authority is published.
   */
  public function isPublished();

  /**
   * Sets the published status of a FSA Authority.
   *
   * @param bool $published
   *   TRUE to set this FSA Authority published, FALSE to set it unpublished.
   *
   * @return \Drupal\fsa_ratings\Entity\FsaAuthorityInterface
   *   The called FSA Authority entity.
   */
  public function setPublished($published);

}
