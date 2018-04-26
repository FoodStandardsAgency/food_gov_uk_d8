<?php

namespace Drupal\fsa_signin;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\taxonomy\Entity\Term;
use Drupal\user\Entity\User;

/**
 * Class SignInService.
 */
class SignInService {

  use StringTranslationTrait;

  const DEFAULT_COUNTRY_CODE = 44;

  /**
   * Constructs a new DefaultService object.
   */
  public function __construct() {

  }

  /**
   * Food alert subscription options.
   *
   * @return array
   *   List of options.
   */
  public function foodAlertsAsOptions() {

    // Initially on "all", compose manually and change to better logic if
    // different food alert types are allowed to subscribe to.
    $alert_types = [
      'all' => $this->t('Food alerts')->render(),
    ];

    $options = [];
    foreach ($alert_types as $key => $name) {
      $options[$key] = $name;
    }

    return $options;
  }

  /**
   * Get user's subscribed food alerts.
   *
   * @param \Drupal\user\Entity\User $account
   *   User account.
   *
   * @return array
   *   Alerts user has subscribed to.
   */
  public function subscribedFoodAlerts(User $account) {
    $subscriptions = $account->get('field_subscribed_food_alerts')
      ->getValue();
    $subscribed_food_alerts = [];
    foreach ($subscriptions as $s) {
      $subscribed_food_alerts[] = $s['value'];
    }
    return $subscribed_food_alerts;
  }

  /**
   * News subscription options.
   *
   * @return array
   *   Array of options in FAPI suitable format.
   */
  public function newsAsOptions() {

    $all_terms = \Drupal::entityTypeManager()
      ->getStorage('taxonomy_term')
      ->loadTree('news_type', 0, 1, FALSE);
    $options = [];
    foreach ($all_terms as $term) {
      $options[$term->tid] = $this->t($term->name)->render();
    }
    return $options;
  }

  /**
   * Consultations subscription options.
   *
   * @return array
   *   Array of options in FAPI suitable format.
   */
  public function consultationsAsOptions() {

    $all_terms = \Drupal::entityTypeManager()
      ->getStorage('taxonomy_term')
      ->loadTree('consultations_type_alerts', 0, 1, FALSE);
    $options = [];
    foreach ($all_terms as $term) {
      $options[$term->tid] = $this->t($term->name)->render();
    }
    return $options;
  }

  /**
   * Get allergen terms as options to subscription forms.
   *
   * @return array
   *   Array of options in FAPI suitable format.
   */
  public function allergenTermsAsOptions() {
    $all_terms = \Drupal::entityTypeManager()
      ->getStorage('taxonomy_term')
      ->loadTree('alerts_allergen', 0, 1, FALSE);
    $options = [];
    foreach ($all_terms as $term) {
      $description = FALSE;
      $full_term = Term::load($term->tid);
      if ($full_term->hasField('field_alternative_label') && $full_term->field_alternative_label->value != '') {
        $langcode = \Drupal::languageManager()->getCurrentLanguage()->getId();
        $localized_term = \Drupal::service('entity.repository')->getTranslationFromContext($full_term, $langcode);
        $description = ' <span class="light">' . $localized_term->field_alternative_label->value . '</span>';
      }
      $options[$term->tid] = $this->t($term->name)->render() . $description;
    }
    return $options;
  }

  /**
   * Get user's subscribed term id's.
   *
   * @param \Drupal\user\Entity\User $account
   *   User object.
   *
   * @return array
   *   Term IDs
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

  /**
   * Unsubscribe user from alerts.
   *
   * @param string $identifier
   *   Phone number OR email address of user to unsubscribe.
   * @param string $values
   *   That to unsubscribe from.
   *
   * @return array
   *   Array with success (bool), uid (int) and message (string).
   */
  public function unsubscribeFromAlerts($identifier, $values) {
    $ret = [
      'uid' => FALSE,
      'success' => FALSE,
      'message' => $this->t('No changes'),
    ];

    if (\Drupal::service('email.validator')->isValid($identifier)) {
      $match_with = 'email';
    }
    else {
      // Assume the identifier is a phone number.
      $match_with = 'phone';
    }

    $values = explode(' ', $values);
    $values = array_map('strtolower', $values);
    $cmd = $values[0];

    // Figure out what user intended to unsubscribe from.
    if (in_array($cmd, ['', 'sms'])) {
      $unsubscribe = 'sms';
    }
    elseif (in_array($cmd, ['all', 'gyd'])) {
      $unsubscribe = 'all';
    }
    elseif (in_array($cmd, ['allergy', 'alergedd'])) {
      $unsubscribe = 'allergy';
    }
    elseif (in_array($cmd, ['food', 'bwyd'])) {
      $unsubscribe = 'food';
    }
    elseif (in_array($cmd, ['news', 'newydd'])) {
      $unsubscribe = 'news';
    }
    elseif (in_array($cmd, ['consultation', 'ymgynghoriad'])) {
      $unsubscribe = 'consultation';
    }
    else {
      // We probably want to unsubscribe per term/tid.
      $unsubscribe = FALSE;
    }

    switch ($match_with) {
      case 'phone':

        // Get user(s) with phone number matching from the callback (match only
        // with the last 7 characters of the phone number.
        $query = \Drupal::entityQuery('user');
        $query->condition('uid', 0, '>');
        $query->condition('status', 1);
        $query->condition('field_notification_sms', '%' . substr($identifier, -7), 'LIKE');
        $uids = $query->execute();
        break;

      case 'email':
        $uids = user_load_by_mail($identifier);
        $uid = $uids->id();

        // Set the array as it would come from entityQuery above.
        $uids = [$uid => $uid];
        break;
    }

    if (!empty($uids)) {
      // Can match multiple users since phone number is not unique field.
      foreach ($uids as $uid) {
        $user = User::load($uid);

        switch ($unsubscribe) {
          case 'sms':
            // SMS unsubscribe removes user phone number preference and the SMS
            // subscribe option maintaining other possible preferences.
            $methods = $user->field_delivery_method->getValue();
            $updated_methods = [];
            foreach ($methods as $method) {
              if ($method['value'] != 'sms') {
                $updated_methods[] = $method['value'];
              }
            }
            $user->field_delivery_method->setValue($updated_methods);
            $user->field_notification_sms->setValue('');
            $user->save();

            $ret['message'] = $this->t('User @uid unsubsribed from SMS (and phone number removed from profile)', ['@uid' => $uid]);
            $ret['success'] = TRUE;
            $ret['uid'] = $uid;
            break;

          case 'all':
            $user->field_subscribed_notifications->setValue([]);
            $user->field_subscribed_food_alerts->setValue([]);
            $user->field_subscribed_news->setValue([]);
            $user->field_subscribed_cons->setValue([]);
            $user->save();

            $ret['message'] = $this->t('User @uid unsubscribed from all alerts', ['@uid' => $uid]);
            $ret['success'] = TRUE;
            $ret['uid'] = $uid;
            break;

          case 'allergy':
            $user->field_subscribed_notifications->setValue([]);
            $user->save();

            $ret['success'] = TRUE;
            $ret['message'] = $this->t('User @uid unsubscribed from all allergy alerts', ['@uid' => $uid]);
            $ret['uid'] = $uid;
            break;

          case 'food':
            $user->field_subscribed_food_alerts->setValue([]);
            $user->save();

            $ret['success'] = TRUE;
            $ret['message'] = $this->t('User @uid unsubscribed from food alerts', ['@uid' => $uid]);
            $ret['uid'] = $uid;
            break;

          case 'news':
            // Unsubscribe from all notifications.
            $user->field_subscribed_news->setValue([]);
            $user->save();

            $ret['success'] = TRUE;
            $ret['message'] = $this->t('User @uid unsubscribed from all news alerts', ['@uid' => $uid]);
            $ret['uid'] = $uid;
            break;

          case 'consultation':
            $user->field_subscribed_cons->setValue([]);
            $user->save();

            $ret['success'] = TRUE;
            $ret['message'] = $this->t('User @uid unsubscribed from all consultation alerts', ['@uid' => $uid]);
            $ret['uid'] = $uid;
            break;

          default:
            $ret['success'] = FALSE;
            $ret['message'] = $this->t('Unsubscribe command @cmd did not match', ['@cmd' => $cmd]);
            $ret['uid'] = $uid;
            break;
        }
      }
    }
    else {
      $ret['success'] = FALSE;
      $ret['message'] = $this->t('Phone or email did not match any user.');
    }

    return $ret;
  }

}
