<?php

namespace Drupal\fsa_ratings_import\Plugin\QueueWorker;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Queue\QueueWorkerBase;
use Drupal\migrate\Plugin\MigrationPluginManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * A queue worker that removes obsolete establishment entities.
 *
 * @QueueWorker(
 *   id = "fsa_ratings_import_entity_purger",
 *   title = @Translation("FSA obsolete entity purger"),
 *   cron = {"time" = 30}
 * )
 */
class FsaRemoveObsoleteEntityPurger extends QueueWorkerBase implements ContainerFactoryPluginInterface {

  /** @var \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager */
  protected $entityTypeManager;

  /** @var \Drupal\migrate\Plugin\MigrationPluginManagerInterface $migrationPluginManager */
  protected $migrationPluginManager;

  /**
   * FsaRemoveObsoleteEntityPurger constructor.
   *
   * @param array $configuration
   * @param string $plugin_id
   * @param mixed $plugin_definition
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   * @param \Drupal\migrate\Plugin\MigrationPluginManagerInterface $migration_plugin_manager
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager, MigrationPluginManagerInterface $migration_plugin_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entity_type_manager;
    $this->migrationPluginManager = $migration_plugin_manager;
  }

  /**
   * Todo: document.
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('plugin.manager.migration')
    );
  }

  /**
   * Purges obsolete entities.
   *
   * @param \stdClass $data
   */
  public function processItem($data) {
    /** @var \Drupal\Core\Entity\ContentEntityStorageInterface $storage */
    $storage = $this->entityTypeManager->getStorage($data->entity_type_id);
    /** @var \Drupal\migrate\Plugin\MigrationInterface $migration_plugin */
    $migration_plugin = $this->migrationPluginManager->createInstance($data->migration_plugin_id);

    foreach ($data->ids as $entity_id => $entity_langcode) {
      if ($entity = $storage->load($entity_id)) {
        $entity->delete();
      }

      // Remove the entry from ID map.
      $migration_plugin->getIdMap()->deleteDestination([
        'id' => $entity_id,
        'langcode' => $entity_langcode,
      ]);
    }
  }

}
