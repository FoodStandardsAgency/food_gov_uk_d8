<?php

namespace Drupal\managed_links\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Managed Link entities.
 *
 * @ingroup managed_links
 */
interface ManagedLinkInterface extends ContentEntityInterface, EntityChangedInterface, EntityOwnerInterface {

  // Add get/set methods for your configuration properties here.

  /**
   * Gets the Managed Link name.
   *
   * @return string
   *   Name of the Managed Link.
   */
  public function getName();

  /**
   * Sets the Managed Link name.
   *
   * @param string $name
   *   The Managed Link name.
   *
   * @return \Drupal\managed_links\Entity\ManagedLinkInterface
   *   The called Managed Link entity.
   */
  public function setName($name);

  /**
   * Gets the Managed Link creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Managed Link.
   */
  public function getCreatedTime();

  /**
   * Sets the Managed Link creation timestamp.
   *
   * @param int $timestamp
   *   The Managed Link creation timestamp.
   *
   * @return \Drupal\managed_links\Entity\ManagedLinkInterface
   *   The called Managed Link entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Returns the Managed Link published status indicator.
   *
   * Unpublished Managed Link are only visible to restricted users.
   *
   * @return bool
   *   TRUE if the Managed Link is published.
   */
  public function isPublished();

  /**
   * Sets the published status of a Managed Link.
   *
   * @param bool $published
   *   TRUE to set this Managed Link to published, FALSE to set it to unpublished.
   *
   * @return \Drupal\managed_links\Entity\ManagedLinkInterface
   *   The called Managed Link entity.
   */
  public function setPublished($published);

}
