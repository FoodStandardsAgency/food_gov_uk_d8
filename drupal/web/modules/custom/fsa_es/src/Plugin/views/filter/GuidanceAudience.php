<?php

namespace Drupal\fsa_es\Plugin\views\filter;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\taxonomy\Plugin\views\filter\TaxonomyIndexTid;
use Drupal\taxonomy\TermStorageInterface;
use Drupal\taxonomy\VocabularyStorageInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @ViewsFilter("fsa_guidance_audience")
 */
class GuidanceAudience extends TaxonomyIndexTid {

  /** @var \Drupal\Core\Language\LanguageManagerInterface $languageManager */
  protected $languageManager;

  /** @var int $depth */
  protected $depth = 2;

  /**
   * GuidanceAudience constructor.
   *
   * @param array $configuration
   * @param string $plugin_id
   * @param mixed $plugin_definition
   * @param \Drupal\taxonomy\VocabularyStorageInterface $vocabulary_storage
   * @param \Drupal\taxonomy\TermStorageInterface $term_storage
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, VocabularyStorageInterface $vocabulary_storage, TermStorageInterface $term_storage, LanguageManagerInterface $language_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $vocabulary_storage, $term_storage);
    $this->languageManager = $language_manager;
  }

  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager')->getStorage('taxonomy_vocabulary'),
      $container->get('entity_type.manager')->getStorage('taxonomy_term'),
      $container->get('language_manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function query() {
  }

  /**
   * {@inheritdoc}
   */
  public function buildExposedForm(&$form, FormStateInterface $form_state) {
    parent::buildExposedForm($form, $form_state);
    $field_identifier = $this->options['expose']['identifier'];

    // Get options.
    $options = $this->getOptions();

    $form[$field_identifier] = [
      '#type' => 'checkboxes',
      '#title' => $this->options['expose']['label'],
      '#options' => array_map(function($item) {
        return $item['name'];
      }, $options),
      '#multiple' => $this->options['expose']['multiple'],
    ];

    // Add depth classes.
    foreach ($options as $name => $option) {
      $form[$field_identifier][$name]['#wrapper_attributes']['class'][] = 'depth-' . $option['depth'];
    }
  }

  /**
   * Prepare an array of term options.
   *
   * @param bool $parent
   *
   * @return array
   */
  protected function getOptions($parent = FALSE) {
    $options = [];

    // Get current language.
    $current_language = $this->languageManager->getCurrentLanguage();

    /** @var \Drupal\elasticsearch_helper_views\Plugin\views\query\Elasticsearch $query */
    $query = $this->view->getQuery();

    // Load
    foreach ($this->termStorage->loadTree($this->options['vid'], 0, $this->depth, TRUE) as $term) {
      // Get translated term.
      $term = $term->getTranslation($current_language->getId());

      // Store name and depth in options array.
      $options[$term->label()] = [
        'name' => $term->label(),
        'depth' => $term->depth,
      ];
    }

    // Get options from Elasticsearch aggregations.
    $aggs_options = $query->getQueryBuilder()->getAudienceFilterOptions();

    // Return only those terms that intersect.
    return array_intersect_key($options, $aggs_options);
  }

}
