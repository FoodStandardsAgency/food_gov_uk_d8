<?php

namespace Drupal\fsa_managed_links\Plugin\Filter;

use Drupal\Component\Utility\Html;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\filter\FilterProcessResult;
use Drupal\filter\Plugin\FilterBase;
use Drupal\linkit\SubstitutionManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a Linkit filter for FSA Managed Link entities.
 *
 * @Filter(
 *   id = "fsa_managed_link_linkit",
 *   title = @Translation("FSA Managed Links URL converter"),
 *   description = @Translation("Updates links inserted by Linkit to point the underlying link field URI."),
 *   type = Drupal\filter\Plugin\FilterInterface::TYPE_TRANSFORM_REVERSIBLE
 * )
 */
class FsaManagedLinkLinkitFilter extends FilterBase implements ContainerFactoryPluginInterface {

  /**
   * The entity repository.
   *
   * @var \Drupal\Core\Entity\EntityRepositoryInterface
   */
  protected $entityRepository;

  /**
   * The substitution manager.
   *
   * @var \Drupal\linkit\SubstitutionManagerInterface
   */
  protected $substitutionManager;

  /**
   * Constructs a FsaManagedLinkLinkitFilter object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entity_repository
   *   The entity repository service.
   * @param \Drupal\linkit\SubstitutionManagerInterface $substitution_manager
   *   The substitution manager.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityRepositoryInterface $entity_repository, SubstitutionManagerInterface $substitution_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->entityRepository = $entity_repository;
    $this->substitutionManager = $substitution_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity.repository'),
      $container->get('plugin.manager.linkit.substitution')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function process($text, $langcode) {
    $result = new FilterProcessResult($text);

    if (strpos($text, 'data-entity-type="fsa_managed_link"') !== FALSE && strpos($text, 'data-entity-uuid') !== FALSE) {
      $dom = Html::load($text);
      $xpath = new \DOMXPath($dom);

      foreach ($xpath->query('//a[@data-entity-type="fsa_managed_link" and @data-entity-uuid]') as $element) {
        /** @var \DOMElement $element */
        try {
          // Load the appropriate translation of the linked entity.
          $entity_type = $element->getAttribute('data-entity-type');
          $uuid = $element->getAttribute('data-entity-uuid');

          $entity = $this->entityRepository->loadEntityByUuid($entity_type, $uuid);
          if ($entity) {

            $entity = $this->entityRepository->getTranslationFromContext($entity, $langcode);
            $link_url = $entity->field_managed_link_url->uri;
            $element->setAttribute('href', $link_url);
            $access = $entity->access('view', NULL, TRUE);

            // Set the appropriate title attribute.
            if ($this->settings['title'] && !$access->isForbidden() && !$element->getAttribute('title')) {
              $element->setAttribute('title', $entity->label());
            }

            // The processed text now depends on:
            $result
              // - the linked entity access for the current user.
              ->addCacheableDependency($access)
              // - the generated URL (which has undergone path & route
              // processing)
              ->addCacheableDependency($link_url)
              // - the linked entity (whose URL and title may change)
              ->addCacheableDependency($entity);
          }
        }
        catch (\Exception $e) {
          watchdog_exception('fsa_managed_link_linkit_filter', $e);
        }
      }

      $result->setProcessedText(Html::serialize($dom));
    }

    return $result;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $form['title'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Automatically set the <code>title</code> attribute to that of the (translated) referenced content'),
      '#default_value' => $this->settings['title'],
      '#attached' => [
        'library' => ['linkit/linkit.filter_html.admin'],
      ],
    ];
    return $form;
  }

}
