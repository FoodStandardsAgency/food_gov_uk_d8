<?php

namespace Drupal\fsa_signin\Event;

use Drupal\user\UserInterface;
use Symfony\Component\EventDispatcher\Event;

class UserUpdatePreferencesEvent extends Event {

  /**
   * User entity.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $user;

  /**
   * Constructs an user event object.
   *
   * @param \Drupal\user\UserInterface $user
   *   Object that implements this interface.
   */
  public function __construct(UserInterface $user) {
    $this->user = $user;
  }

  /**
   * Get the inserted user.
   *
   * @return \Drupal\user\UserInterface
   *   Returns user object.
   */
  public function getUser() {
    return $this->user;
  }

}
