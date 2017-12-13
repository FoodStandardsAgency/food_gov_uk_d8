<?php

namespace Drupal\fsa_es\Plugin\ElasticsearchQueryBuilder;

use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\elasticsearch_helper_views\Plugin\ElasticsearchQueryBuilder\ElasticsearchQueryBuilderPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class SitewideSearchBase
 */
abstract class SitewideSearchBase extends ElasticsearchQueryBuilderPluginBase {

  /** @var \Drupal\Core\Language\LanguageInterface $currentLanguage */
  protected $currentLanguage;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, LanguageManagerInterface $language_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->currentLanguage = $language_manager->getCurrentLanguage();
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('language_manager')
    );
  }

}
