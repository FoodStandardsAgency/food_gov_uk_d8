<?php

namespace Drupal\fsa_webform;

use Drupal\Core\Form\FormStateInterface;
use Drupal\webform\WebformSubmissionInterface;

/**
 * Class WebformSubmissionSendHanWebformSubmissionMessageHandlerdler
 */
class WebformSubmissionMessageHandler {

  /**
   * @var string
   */
  public $messageHandlerId;

  /**
   * Sets message handler ID.
   *
   * @param string $message_handler_id
   *   The message handler id.
   */
  public function setMessageHandlerId($message_handler_id) {
    $this->messageHandlerId = $message_handler_id;
  }

  /**
   * Sends the submission using given message handler.
   *
   * @param array $form
   *   The form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state interface.
   */
  public function sendWebformSubmission(array $form, FormStateInterface $form_state) {
    $form_object = $form_state->getFormObject();
    /** @var \Drupal\webform\WebformSubmissionInterface $webform_submission */
    $webform_submission = $form_object->getEntity();

    if ($message_handler = $this->getMessageHandler($webform_submission)) {
      // Get message.
      $message = $this->getMessage($webform_submission);
      // Send the message.
      $message_handler->sendMessage($webform_submission, $message);
    }
  }

  /**
   * Returns webform message handler.
   *
   * @param \Drupal\webform\WebformSubmissionInterface $webform_submission
   *   Webform submission interface.
   * @param string|null $handler
   *   Optional handler reference.
   *
   * @return \Drupal\webform\Plugin\WebformHandlerInterface
   *   The webform handler interface.
   */
  public function getMessageHandler(WebformSubmissionInterface $webform_submission, $handler = NULL) {
    $handler = $handler ? $handler : $this->messageHandlerId;
    return $webform_submission->getWebform()->getHandler($handler);
  }

  /**
   * Returns message configuration (to, from, subject, body).
   *
   * @param \Drupal\webform\WebformSubmissionInterface $webform_submission
   *   Webform submission interface.
   * @return array
   *   Array of results.
   */
  public function getMessage(WebformSubmissionInterface $webform_submission) {
    $result = [];

    /** @var \Drupal\webform\Plugin\WebformHandlerMessageInterface $message_handler */
    $message_handler = $this->getMessageHandler($webform_submission);

    if ($message = $message_handler->getMessage($webform_submission)) {
      $keys = [
        'to_mail',
        'from_mail',
        'from_name',
        'subject',
        'body',
        'reply_to',
        'return_path',
        'html',
      ];

      foreach ($keys as $key) {
        $result[$key] = isset($message[$key]) ? $message[$key] : NULL;
      }
    }

    return $result;
  }

}
