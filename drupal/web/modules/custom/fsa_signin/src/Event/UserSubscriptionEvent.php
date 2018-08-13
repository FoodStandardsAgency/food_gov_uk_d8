<?php

namespace Drupal\fsa_signin\Event;

use Drupal\user\UserInterface;
use Symfony\Component\EventDispatcher\Event;

class UserSubscriptionEvent extends Event {

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
   */
  public function __construct(UserInterface $user) {
    $this->user = $user;
  }

  /**
   * Get the inserted user.
   *
   * @return \Drupal\user\UserInterface
   */
  public function getUser() {
    return $this->user;
  }
}
