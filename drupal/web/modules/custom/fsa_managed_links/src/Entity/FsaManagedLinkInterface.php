<?php

namespace Drupal\fsa_managed_links\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining FSA Managed Link entities.
 *
 * @ingroup fsa_managed_links
 */
interface FsaManagedLinkInterface extends ContentEntityInterface, EntityChangedInterface, EntityOwnerInterface {

  // Add get/set methods for your configuration properties here.

  /**
   * Gets the FSA Managed Link name.
   *
   * @return string
   *   Name of the FSA Managed Link.
   */
  public function getName();

  /**
   * Sets the FSA Managed Link name.
   *
   * @param string $name
   *   The FSA Managed Link name.
   *
   * @return \Drupal\fsa_managed_links\Entity\FsaManagedLinkInterface
   *   The called FSA Managed Link entity.
   */
  public function setName($name);

  /**
   * Gets the FSA Managed Link creation timestamp.
   *
   * @return int
   *   Creation timestamp of the FSA Managed Link.
   */
  public function getCreatedTime();

  /**
   * Sets the FSA Managed Link creation timestamp.
   *
   * @param int $timestamp
   *   The FSA Managed Link creation timestamp.
   *
   * @return \Drupal\fsa_managed_links\Entity\FsaManagedLinkInterface
   *   The called FSA Managed Link entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Returns the FSA Managed Link published status indicator.
   *
   * Unpublished FSA Managed Link are only visible to restricted users.
   *
   * @return bool
   *   TRUE if the FSA Managed Link is published.
   */
  public function isPublished();

  /**
   * Sets the published status of a FSA Managed Link.
   *
   * @param bool $published
   *   TRUE to set this FSA Managed Link to published, FALSE to set it to unpublished.
   *
   * @return \Drupal\fsa_managed_links\Entity\FsaManagedLinkInterface
   *   The called FSA Managed Link entity.
   */
  public function setPublished($published);

}
