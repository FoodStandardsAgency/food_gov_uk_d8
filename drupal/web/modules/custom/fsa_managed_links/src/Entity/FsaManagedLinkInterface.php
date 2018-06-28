<?php

namespace Drupal\fsa_managed_links\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining FSA managed link entities.
 *
 * @ingroup fsa_managed_links
 */
interface FsaManagedLinkInterface extends ContentEntityInterface, EntityChangedInterface, EntityOwnerInterface {

  // Add get/set methods for your configuration properties here.

  /**
   * Gets the FSA managed link name.
   *
   * @return string
   *   Name of the FSA managed link.
   */
  public function getName();

  /**
   * Sets the FSA managed link name.
   *
   * @param string $name
   *   The FSA managed link name.
   *
   * @return \Drupal\fsa_managed_links\Entity\FsaManagedLinkInterface
   *   The called FSA managed link entity.
   */
  public function setName($name);

  /**
   * Gets the FSA managed link creation timestamp.
   *
   * @return int
   *   Creation timestamp of the FSA managed link.
   */
  public function getCreatedTime();

  /**
   * Sets the FSA managed link creation timestamp.
   *
   * @param int $timestamp
   *   The FSA managed link creation timestamp.
   *
   * @return \Drupal\fsa_managed_links\Entity\FsaManagedLinkInterface
   *   The called FSA managed link entity.
   */
  public function setCreatedTime($timestamp);

}
