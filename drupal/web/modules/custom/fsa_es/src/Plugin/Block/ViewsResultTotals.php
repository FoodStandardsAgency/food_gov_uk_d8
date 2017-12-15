<?php

namespace Drupal\fsa_es\Plugin\Block;

use Drupal\Component\Utility\Html;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\SubformStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'Footer logo' Block.
 *
 * @Block(
 *   id = "views_result_totals",
 *   admin_label = @Translation("Views result totals")
 * )
 */
class ViewsResultTotals extends BlockBase implements ContainerFactoryPluginInterface {

  /** @var \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager */
  protected $entityTypeManager;

  /**
   * ViewsResultTotals constructor.
   *
   * @param array $configuration
   * @param string $plugin_id
   * @param mixed $plugin_definition
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    $configuration = parent::defaultConfiguration();
    $configuration['view_name'] = '';

    return $configuration;
  }

  /**
   * {@inheritdoc}
   */
  function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);

    /** @var \Drupal\views\Entity\View[] $views */
    $views = \Drupal::entityTypeManager()->getStorage('view')->loadMultiple();

    $form['view_name'] = [
      '#type' => 'select',
      '#title' => $this->t('View'),
      '#description' => $this->t('Select the view result of which will be displayed.'),
      '#options' => array_map(function($view) {
        /** @var \Drupal\views\Entity\View $view */
        return $view->label();
      }, $views),
      '#default_value' => $this->configuration['view_name'],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);

    $settings = ['view_name'];

    // Special handling for regular block placement into site regions as there
    // are two forms involved (parent form).
    if ($form_state instanceof SubformStateInterface) {
      $complete_form_state = $form_state->getCompleteFormState();

      // Store settings if values are set.
      foreach ($settings as $setting) {
        if ($setting_value = $complete_form_state->getValue(['settings', $setting])) {
          $this->configuration[$setting] = $setting_value;
        }
      }
    }
    else {
      foreach ($settings as $setting) {
        $this->configuration[$setting] = $form_state->getValue($setting);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    /** @var \Drupal\views\Entity\View $view */
    $view = $this->entityTypeManager->getStorage('view')->load($this->configuration['view_name']);

    return array(
      '#type' => 'inline_template',
      '#template' => '<div class="{{ class_name }}"></div>',
      '#context' => ['class_name' => Html::getClass('views-result-total-' . $view->id())],
    );
  }

}
