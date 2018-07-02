<?php

namespace Drupal\fsa_contactus_data\Plugin\WebformHandler;

use Drupal\webform\Plugin\WebformHandlerBase;
use Drupal\webform\WebformSubmissionInterface;

/**
 * Webform submission test handler.
 *
 * @WebformHandler(
 *   id = "fsa_contactus_data_handler",
 *   label = @Translation("FSA Contact Us form data handler"),
 *   category = @Translation("FSA"),
 *   description = @Translation("FSA Contact Us form data handler (stores submissions without personal data to a file)"),
 *   cardinality = \Drupal\webform\Plugin\WebformHandlerInterface::CARDINALITY_SINGLE,
 *   results = \Drupal\webform\Plugin\WebformHandlerInterface::RESULTS_IGNORED,
 *   submission = \Drupal\webform\Plugin\WebformHandlerInterface::SUBMISSION_REQUIRED,
 * )
 */
class FSAContactUsHandler extends WebformHandlerBase {

  /**
   * {@inheritdoc}
   */
  public function postSave(WebformSubmissionInterface $webform_submission, $update = TRUE) {
    $is_completed = ($webform_submission->getState() == WebformSubmissionInterface::STATE_COMPLETED);

    if ($is_completed) {
      // @var array $values
      // Collect the submission values into this array for statistical usage.
      $values = [];

      // @var array $personal_data_field_names
      // The field names which should be excluded from statistical data
      // collection. Can be defined in the settings.php with
      // $config['fsa_contactus_data']['excluded_field_names'];
      $personal_data_field_names = \Drupal::config('fsa_contactus_data')->get('excluded_field_names');
      if (empty($personal_data_field_names)) {
        $personal_data_field_names = ['name', 'email', 'address', 'phone'];
      }

      // Prepare the values and store them.
      $fields = $webform_submission->toArray(TRUE);

      // Load the webform with element (custom) properties to check which may
      // be set as data and append to $personal_data_field_names array.
      $webform = \Drupal::entityTypeManager()->getStorage('webform')->load($fields['webform_id']);
      foreach ($webform->getElementsDecodedAndFlattened() as $e_key => $elem) {
        if (isset($elem['#personal_data']) && $elem['#personal_data'] === TRUE) {
          $personal_data_field_names[] = $e_key;
        }
      }

      foreach ($fields['data'] as $field_name => $field_value) {
        // Don't process the value when the field name is defined in the
        // excluded fields' list.
        if (in_array($field_name, $personal_data_field_names)) {
          continue;
        }

        // Select fields, checkboxes and similar multi value fields are arrays.
        if (is_array($field_value)) {
          $tmp = [];
          foreach ($field_value as $arr_field_value) {
            $tmp[] = $arr_field_value;
          }
          // Concatenate multi value field into single string separated with
          // commas.
          $values[$field_name] = implode(', ', $tmp);
        }
        else {
          $values[$field_name] = $field_value;
        }
      }

      // Use the form id to separate submissions of each different forms.
      $identifier = 'form_' . $webform_submission->getWebform()->id();

      // Store anonymous submission data per each form.
      $this->saveDataToFile($identifier, implode(';', $values));
    }
  }

  /**
   * Store submission data to a file.
   *
   * @param string $form_id
   *   The id of the form.
   * @param string $value
   *   Comma separated values from an array of submissio values.
   */
  protected function saveDataToFile($form_id, $value) {

    // Retrieve the system file path to the file.
    $uri = "private://contact_us_submission_$form_id";
    $stream_wrapper_manager = \Drupal::service('stream_wrapper_manager')->getViaUri($uri);
    $path = $stream_wrapper_manager->realpath();

    // Write the contents to the file, using the FILE_APPEND flag to append the
    // content to the end of the file and the LOCK_EX flag to prevent anyone
    // else writing to the file at the same time.
    file_put_contents($path, $value . PHP_EOL, FILE_APPEND | LOCK_EX);
  }

}
