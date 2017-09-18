<?php

namespace Drupal\fsa_signin;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\user\Entity\User;

/**
 * Class SignInService.
 */
class SignInService {

  use StringTranslationTrait;

  /**
   * Constructs a new DefaultService object.
   */
  public function __construct() {

  }


  /**
   * @return array
   */
  public function allergenTermsAsOptions() {
    $all_terms = \Drupal::entityTypeManager()
      ->getStorage('taxonomy_term')
      ->loadTree('alerts_allergen', 0, 1, FALSE);
    $options = [];
    foreach ($all_terms as $term) {
      $options[$term->tid] = $this->t($term->name)->render();
    }
    return $options;
  }

  /**
   * @param \Drupal\user\Entity\User $account
   * @return int[] Term IDs
   */
  public function subscribedTermIds(User $account) {
    $subscriptions = $account->get('field_subscribed_notifications')
      ->getValue();
    $subscribed_term_ids = [];
    foreach ($subscriptions as $s) {
      $subscribed_term_ids[] = intval($s['target_id']);
    }
    return $subscribed_term_ids;
  }
}
