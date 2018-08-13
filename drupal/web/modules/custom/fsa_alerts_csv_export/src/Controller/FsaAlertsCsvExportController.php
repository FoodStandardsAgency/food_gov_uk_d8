<?php

namespace Drupal\fsa_alerts_csv_export\Controller;

use Drupal\Core\Cache\CacheableResponse;
use Drupal\Core\Controller\ControllerBase;
use League\Csv\Writer;

class FsaAlertsCsvExportController extends ControllerBase {

  /**
   * Controller callback to build and return the CSV file response.
   *
   * @return \Drupal\Core\Cache\CacheableResponse
   *   A cacheable response object.
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \League\Csv\CannotInsertRecord
   */
  public function generateCsv() {
    $headers = [
      'Active users',
      'Allergy alerts: email',
      'Allergy alerts: SMS',
    ];

    $row = [
      $this->getTotalUserActiveCount(),
      $this->getUsersSubscribedToAllergyAlerts('email'),
      $this->getUsersSubscribedToAllergyAlerts('sms'),
    ];

    // Get terms from the 'Alerts: Allergen' vocabulary.
    $vocabulary = \Drupal::entityTypeManager()->getStorage('taxonomy_vocabulary')->load('alerts_allergen');
    $allergy_terms = \Drupal::entityTypeManager()
      ->getStorage('taxonomy_term')
      ->loadTree($vocabulary->id());

    foreach ($allergy_terms as $term) {
      $headers[] = $vocabulary->label() . ': ' . $term->name;
      $row[] = $this->getUsersSubscribedToAlertCategory($term->tid, 'field_subscribed_notifications');
    }

    // Get terms from the 'Consultation types alerts' vocabulary.
    $vocabulary = \Drupal::entityTypeManager()->getStorage('taxonomy_vocabulary')->load('consultations_type_alerts');
    $consultation_terms = \Drupal::entityTypeManager()
      ->getStorage('taxonomy_term')
      ->loadTree($vocabulary->id());

    foreach ($consultation_terms as $term) {
      $headers[] = $vocabulary->label() . ': ' . $term->name;
      $row[] = $this->getUsersSubscribedToAlertCategory($term->tid, 'field_subscribed_cons');
    }

    // Get terms from the 'News type' vocabulary.
    $vocabulary = \Drupal::entityTypeManager()->getStorage('taxonomy_vocabulary')->load('news_type');
    $newstype_terms = \Drupal::entityTypeManager()
      ->getStorage('taxonomy_term')
      ->loadTree($vocabulary->id());

    foreach ($newstype_terms as $term) {
      $headers[] = $vocabulary->label() . ': ' . $term->name;
      $row[] = $this->getUsersSubscribedToAlertCategory($term->tid, 'field_subscribed_news');
    }

    // Create a new file stream to allow us to build up the CSV output.
    $writer = Writer::createFromStream(tmpfile());
    $writer->insertOne($headers);
    $writer->insertOne($row);

    // Return the response.
    return new CacheableResponse($writer->getContent(), 200, [
      'Content-Encoding' => 'none',
      'Content-Type' => 'text/csv; charset=UTF-8',
      // TODO: make filename dynamic.
      'Content-Disposition' => 'attachment; filename="alerts_report.csv"',
      'Content-Description' => 'File Transfer',
    ]);
  }

  /**
   * Function to count total number of subscribed allergy alert users
   * by delivery method.
   *
   * @param string $type
   *   The type of alert delivery method.
   * @return int
   *   The count of matching users.
   */
  public function getUsersSubscribedToAllergyAlerts(string $type) {
    if (!in_array($type, ['sms', 'email'])) {
      return 0;
    }

    $user_count = \Drupal::service('entity.query')
      ->get('user')
      ->condition('field_subscribed_food_alerts', 'all')
      ->condition('field_delivery_method', $type)
      ->count()
      ->execute();

    return $user_count;
  }

  /**
   * Count all active users on the site.
   *
   * @return int
   *   The count of matching users.
   */
  public function getTotalUserActiveCount() {
    $user_count = \Drupal::service('entity.query')
      ->get('user')
      ->condition('status', 1)
      ->count()
      ->execute();

    return empty($user_count) ? 0 : $user_count;
  }

  /**
   * Count all users who subscribe to a specific alert category.
   *
   * @param int $tid
   *   Term ID.
   * @param string $field
   *   Field machine key.
   *
   * @return int
   *   The count of matching users.
   */
  public function getUsersSubscribedToAlertCategory(int $tid, string $field) {
    if (empty($tid) || is_numeric($tid) === FALSE) {
      return 0;
    }

    $user_count = \Drupal::service('entity.query')
      ->get('user')
      ->condition($field, $tid)
      ->count()
      ->execute();

    return $user_count;
  }

}
