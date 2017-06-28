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

    $default_copy = '<h2>What are the hygiene ratings?</h2><p>Food hygiene ratings help you to choose where to eat out or shop for food by telling you how seriously the business takes their food hygiene standards.</p><p>The ratings are given by local authorities in England, Northern Ireland and Wales and they apply to restaurants, pubs, cafes, takeaways, hotels, supermarkets and other food shops.</p><p>The food hygiene rating reflects the hygiene standards found at the time the business is inspected by a food safety officer. These officers are specially trained to assess food hygiene standards.</p><p>These are the three elements that a food hygiene rating is based on</p><ul><li>how hygienically the food is handled – how it is prepared, cooked, re-heated, cooled and stored</li><li>the condition of the structure of the buildings – the cleanliness, layout, lighting, ventilation and other facilities</li><li>how the business manages what it does to make sure food is safe and so that the officer can be confident standards will be maintained in the future</li></ul><p>To get the top rating of ‘5’, businesses must do well in all three elements.</p><p>Those with ratings of ‘0’ are very likely to be performing poorly in all three elements and are likely to have a history of serious problem.</p><p><a href="#">Find out more about food hygiene ratings</a></p>';

    // Ratings form landing info content.
    // @todo: Use to WYSIWYG field?
    $form['fsa_ratings']['ratings_info_content'] = array(
      '#type' => 'text_format',
      '#format' => 'full_html',
      '#title' => t('Ratings search landing content'),
      '#maxlength' => NULL,
      '#default_value' => $fsa_ratings->get('ratings_info_content') ? $fsa_ratings->get('ratings_info_content') : $default_copy,
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
      ->set('ratings_info_content', $form_state->getValue('ratings_info_content')['value'])
      ->save();
    parent::submitForm($form, $form_state);
  }
}