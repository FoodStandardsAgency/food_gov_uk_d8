<?php
namespace Drupal\fsa_messaging\Form;

// Classes referenced in this class:
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\ConfigFormBase;

/**
 * Configure site information settings for this site.
 */
class FsaMessagingConfigForm extends ConfigFormBase {
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'fsa_messaging_config_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state)  {

    $config = \Drupal::config('fsa_messaging.settings');
    $message = $config->get('fsa_messaging_message');

    $form['fsa_messaging'] = [
      '#type' => 'details',
      '#title' => t('Site wide message'),
      '#open' => TRUE,

      'fsa_messaging_active' => [
        '#type' => 'checkbox',
        '#title' => t('Display message'),
        '#default_value' => $config->get('fsa_messaging_active')
      ],
      'fsa_messaging_style' => [
        '#type' => 'select',
        '#title' => t('Message style'),
        '#options' => [
          'default' => t('Default'),
          'warning' => t('Highlighted'),
        ],
        '#default_value' => $config->get('fsa_messaging_style')
      ],
      'fsa_messaging_message' => [
        '#type' => 'text_format',
        '#title' => t('Message'),
        '#format' => $message['format'],
        '#default_value' => $message['value']
      ],
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $config = \Drupal::service('config.factory')->getEditable('fsa_messaging.settings');

    foreach (['fsa_messaging_active', 'fsa_messaging_style', 'fsa_messaging_message'] as $key) {
      $config->set($key, $form_state->getValue($key));
    }
    $config->save();

  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'fsa_messaging.settings',
    ];
  }

}
?>
