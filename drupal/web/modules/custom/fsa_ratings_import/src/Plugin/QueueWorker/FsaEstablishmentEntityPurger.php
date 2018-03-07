<?php

namespace Drupal\fsa_ratings_import\Plugin\QueueWorker;

use Drupal\Core\Database\Connection;
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
 *   title = @Translation("FSA establishment entity purger"),
 *   cron = {"time" = 30}
 * )
 */
class FsaEstablishmentEntityPurger extends QueueWorkerBase implements ContainerFactoryPluginInterface {

  /** @var \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager */
  protected $entityTypeManager;

  /** @var \Drupal\migrate\Plugin\MigrationPluginManagerInterface $migrationPluginManager */
  protected $migrationPluginManager;

  /** @var \Drupal\Core\Database\Connection $database */
  protected $database;

  /**
   * FsaEstablishmentEntityPurger constructor.
   *
   * @param array $configuration
   * @param string $plugin_id
   * @param mixed $plugin_definition
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   * @param \Drupal\migrate\Plugin\MigrationPluginManagerInterface $migration_plugin_manager
   * @param \Drupal\Core\Database\Connection $database
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager, MigrationPluginManagerInterface $migration_plugin_manager, Connection $database) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entity_type_manager;
    $this->migrationPluginManager = $migration_plugin_manager;
    $this->database = $database;
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
      $container->get('plugin.manager.migration'),
      $container->get('database')
    );
  }

  protected function getMigrationTables($migration_plugin_id) {
    return [
      'map' => 'migrate_map_' . $migration_plugin_id,
      'message' => 'migrate_message_' . $migration_plugin_id,
    ];
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
    $map_table = $migration_plugin->getIdMap()->mapTableName();
    $message_table = $migration_plugin->getIdMap()->messageTableName();

    foreach ($data->ids as $entity_id => $entity_langcode) {
      if ($entity = $storage->load($entity_id)) {
        $entity->delete();
      }

      // In order to clean the migration map, a specific entry is located by
      // entity destination ID and language code, then manually removed from
      // migration map and message table. The reason for manual process is that
      // with migration the process is rather slow, but the end result is the
      // same (migration events are not fired, though).

      // Get source ID hash.
      $query = $this->database->select($map_table, 'm')
        ->fields('m', ['source_ids_hash']);
      $query->condition('destid1', $entity_id);
      $query->condition('destid2', $entity_langcode);

      // If source ID has if found, remove from map and message tables.
      if ($source_ids_hash = $query->execute()->fetchField()) {
        // Remove a row from map table.
        $this->database->delete($map_table)
          ->condition('source_ids_hash', $source_ids_hash)
          ->execute();

        // Remove a row from message table.
        $this->database->delete($message_table)
          ->condition('source_ids_hash', $source_ids_hash)
          ->execute();
      }
    }
  }

}
