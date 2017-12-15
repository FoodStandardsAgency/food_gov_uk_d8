<?php

namespace Drupal\fsa_custom\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;

/**
 * FSA Custom settings page.
 */
class FsaSettings extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'fsa_custom.settings';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    // Wrapper to define common links used accross the site.
    $form['links'] = [
      '#type' => 'details',
      '#title' => $this->t('Link targets'),
      '#description' => $this->t('Define link targets for common pages linked from modules and page template.'),
      '#open' => TRUE,
    ];
    $contact_link = \Drupal::state()->get('fsa_custom.contact_link');
    $form['links']['contact_link'] = [
      '#title' => $this->t('Main contact lander'),
      '#description' => $this->t('Contact page main lander, used on heading link.'),
      '#type' => 'entity_autocomplete',
      '#target_type' => 'node',
      '#default_value' => (isset($contact_link) && is_numeric($contact_link)) ? Node::load($contact_link) : '',
    ];
    $privacy_link = \Drupal::state()->get('fsa_custom.privacy_link');
    $form['links']['privacy_link'] = [
      '#title' => $this->t('Privacy page'),
      '#description' => $this->t('Privacy description page.'),
      '#type' => 'entity_autocomplete',
      '#target_type' => 'node',
      '#default_value' => (isset($privacy_link) && is_numeric($privacy_link)) ? Node::load($privacy_link) : '',
    ];

    $form['actions'] = [
      '#type' => 'actions',
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save'),
      '#button_type' => 'primary',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    // Store contact link target.
    $contact_link = $form_state->getValue('contact_link');
    \Drupal::state()->set('fsa_custom.contact_link', $contact_link);

    // Store Privacy policy link target.
    $privacy_link = $form_state->getValue('privacy_link');
    \Drupal::state()->set('fsa_custom.privacy_link', $privacy_link);

    // Let the user know something happened.
    drupal_set_message($this->t('Custom settings updated.'));

  }

}
