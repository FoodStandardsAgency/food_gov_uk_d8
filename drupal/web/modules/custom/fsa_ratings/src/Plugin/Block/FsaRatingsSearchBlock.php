<?php

namespace Drupal\fsa_ratings\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * FSA Ratings search form in a block.
 *
 * @Block(
 *   id = "fsa_ratings_search_block",
 *   admin_label = @Translation("FSA Ratings search block"),
 * )
 */
class FsaRatingsSearchBlock extends BlockBase implements ContainerFactoryPluginInterface {

  protected $formBuilder;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, FormBuilderInterface $formBuilder) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->formBuilder = $formBuilder;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('form_builder')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    return [
      '#title' => $this->t('Food hygiene ratings search'),
      '#theme' => 'fsa_ratings_search_page',
      '#form' => $this->formBuilder->getForm('Drupal\fsa_ratings\Form\FsaRatingsSearchForm'),
    ];
  }

}
