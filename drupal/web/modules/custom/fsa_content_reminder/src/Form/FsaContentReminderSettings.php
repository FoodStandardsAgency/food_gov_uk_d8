<?php

namespace Drupal\fsa_content_reminder\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class FsaContentReminderSettings.
 */
class FsaContentReminderSettings extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'fsa_content_reminder.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'fsa_content_reminder_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('fsa_content_reminder.settings');

    $form['info'] = [
      '#markup' => '<p>' . $this->t('Content reminders are sent to the email below. Reminders are based on the "Content reminder" field date value on node edit form.') . '<br />' .
      $this->t('Reminders are sent only if the node is published at the time of reminder dispatching.') . '</p>' .
      '<p>' . $this->t('<a href="@url">List of pages pending content review</a>', ['@url' => '/admin/content/content-reminders']) . '</p>',
    ];
    $form['email'] = [
      '#type' => 'email',
      '#title' => $this->t('Content reminder email'),
      '#description' => $this->t('Leave empty to disable reminder emails.'),
      '#default_value' => $config->get('email'),
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $email = $form_state->getValue('email');
    if ($email != '' && !\Drupal::service('email.validator')->isValid($email)) {
      $form_state->setErrorByName('email', $this->t('Email value is not valid.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $this->config('fsa_content_reminder.settings')
      ->set('email', $form_state->getValue('email'))
      ->save();
  }

}
