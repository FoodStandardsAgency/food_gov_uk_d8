<?php
/**
 * @file
 * Contains \Drupal\fsa_ratings\Form\FsaRatingsConfigurations.
 */

namespace Drupal\fsa_ratings\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\ConfigFormBase;

/**
 * FSA Ratings feature configurations.
 */
class FsaRatingsConfigurations extends ConfigFormBase {

  /**
   * Constructor for FsaRatingsConfigurations.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   */
  public function __construct(ConfigFactoryInterface $config_factory) {
    parent::__construct($config_factory);
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'fsa_ratings_admin_form';
  }

  /**
   * Gets the configuration names that will be editable.
   *
   * @return array
   * An array of configuration object names that are editable if called in
   * conjunction with the trait's config() method.
   */
  protected function getEditableConfigNames() {
    return ['config.fsa_ratings'];
  }

  /**
   * Form constructor.
   *
   * @param array $form
   * An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   * The current state of the form.
   *
   * @return array
   * The form structure.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $fsa_ratings = $this->config('config.fsa_ratings');

    // Ratings form landing info content.
    $form['fsa_ratings']['ratings_info_content'] = array(
      '#type' => 'textarea',
      '#title' => t('Ratings search landing content'),
      '#maxlength' => NULL,
      '#default_value' => $fsa_ratings->get('ratings_info_content'),
      '#description' => t('Informational content displayed on the Ratings search page'),
    );
    return parent::buildForm($form, $form_state);
  }

  /**
   * Form submission handler.
   *
   * @param array $form
   * An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   * The current state of the form.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('config.fsa_ratings')
      ->set('ratings_info_content', $form_state->getValue('ratings_info_content'))
      ->save();
    parent::submitForm($form, $form_state);
  }
}