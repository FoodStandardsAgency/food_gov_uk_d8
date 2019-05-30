<?php

namespace Drupal\fsa_es\Plugin\Block;

use Drupal\Component\Utility\Html;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\SubformStateInterface;
use Drupal\Core\Render\Element;
use Drupal\Core\Routing\CurrentRouteMatch;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;
use Drupal\views\Plugin\Block\ViewsExposedFilterBlock;
use Drupal\views\ViewExecutableFactory;
use Drupal\Core\Entity\EntityStorageInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class ViewsExposedFilterSearchBlock.
 *
 * This block displays only keyword field on search view's exposed form.
 */
class ViewsExposedFilterSearchBlock extends ViewsExposedFilterBlock {

  /**
   * @var \Drupal\Core\Routing\CurrentRouteMatch
   */
  protected $currentRouteMatch;

  /**
   * ViewsExposedFilterSearchBlock constructor.
   *
   * @param array $configuration
   *   Configuration data.
   * @param string $plugin_id
   *   Plugin ID.
   * @param mixed $plugin_definition
   *   Plugin definition data.
   * @param \Drupal\views\ViewExecutableFactory $executable_factory
   *   ViewExecutableFactory object.
   * @param \Drupal\Core\Entity\EntityStorageInterface $storage
   *   EntityStorageInterface.
   * @param \Drupal\Core\Session\AccountInterface $user
   *   AccountInterface for user data.
   * @param \Drupal\Core\Routing\CurrentRouteMatch $current_route_match
   *   CurrentRouteMatch object.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ViewExecutableFactory $executable_factory, EntityStorageInterface $storage, AccountInterface $user, CurrentRouteMatch $current_route_match) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $executable_factory, $storage, $user);

    $this->currentRouteMatch = $current_route_match;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration, $plugin_id, $plugin_definition,
      $container->get('views.executable'),
      $container->get('entity_type.manager')->getStorage('view'),
      $container->get('current_user'),
      $container->get('current_route_match')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    $configuration = parent::defaultConfiguration();
    $configuration['filters'] = [];
    $configuration['post_route_name'] = '';
    $configuration['location_context'] = '';
    $configuration['disable_ajax'] = FALSE;
    $configuration['hide_submit_button'] = FALSE;

    return $configuration;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);

    // ViewsBlockBase disables the label field. This brings it back.
    $form['label']['#default_value'] = $this->label();
    $form['label']['#access'] = TRUE;

    $this->view->initHandlers();

    $filter_options = [];
    foreach ($this->view->filter as $filter) {
      $id = $filter->exposedInfo()['value'];
      $filter_options[$id] = $filter->adminLabel();
    }

    $form['filters'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Visible filters'),
      '#description' => $this->t('Select exposed filters that should be visible to the user.'),
      '#options' => $filter_options,
      '#default_value' => $this->configuration['filters'],
    ];

    $form['post_route_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('POST route name'),
      '#description' => $this->t('Define the route name to which POST action should be performed.'),
      '#default_value' => $this->configuration['post_route_name'],
    ];

    $form['location_context'] = [
      '#title' => $this->t('Location context'),
      '#type' => 'textfield',
      '#default_value' => !empty($this->configuration['location_context']) ? $this->configuration['location_context'] : '',
      '#description' => $this->t('Provide a location context.'),
    ];

    $form['disable_ajax'] = [
      '#title' => $this->t('Disable Ajax'),
      '#type' => 'checkbox',
      '#default_value' => !empty($this->configuration['disable_ajax']) ? $this->configuration['disable_ajax'] : '',
      '#description' => $this->t('If checked Ajax is disabled on exposed form.'),
    ];

    $form['hide_submit_button'] = [
      '#title' => $this->t('Hide submit button'),
      '#type' => 'checkbox',
      '#default_value' => !empty($this->configuration['hide_submit_button']) ? $this->configuration['hide_submit_button'] : '',
      '#description' => $this->t('If checked submit button is hidden.'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);

    $settings = [
      'filters',
      'post_route_name',
      'location_context',
      'disable_ajax',
      'hide_submit_button',
    ];

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
    $build = parent::build();

    try {
      // Hide fields that are explicitly defined.
      foreach (Element::children($build) as $key) {
        if (array_key_exists($key, $this->configuration['filters']) && !in_array($key, $this->configuration['filters'], TRUE)) {
          $build[$key]['#access'] = FALSE;
        }
      }

      // If Ajax needs to be disabled for the form, "views-exposed-form-"
      // prefix needs to be removed from form ID.
      //
      // @see core/modules/views/js/ajax_view.js where view instance is
      // based on form selector.
      if (!empty($this->configuration['disable_ajax'])) {
        $build['#id'] = str_replace('views-exposed-form-', '', $build['#id']);
        unset($build['#attributes']['data-bef-auto-submit-full-form']);
      }

      // Hide submit button.
      if (!empty($this->configuration['hide_submit_button'])) {
        $build['actions']['#attributes']['class'][] = 'js-hide';
      }

      if (!empty($this->configuration['post_route_name'])) {
        $parameters = $this->currentRouteMatch->getRawParameters()->all();

        $url = Url::fromRoute($this->configuration['post_route_name'], $parameters);
        $url->setAbsolute();
        $build['#action'] = $url->toString();
      }

      // Add location context based theme as a suggestion.
      if (!empty($this->configuration['location_context'])) {
        $original_theme = !empty($build['#theme']) ? end($build['#theme']) : 'views_exposed_form';
        array_unshift($build['#theme'], $original_theme . '__location_context__' . Html::escape($this->configuration['location_context']));
      }
    }
    catch (\Exception $e) {
      watchdog_exception('fsa_es', $e);
    }

    return $build;
  }

}
