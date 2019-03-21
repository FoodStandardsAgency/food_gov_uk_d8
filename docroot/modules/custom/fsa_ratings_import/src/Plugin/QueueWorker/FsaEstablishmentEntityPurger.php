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

  /**
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * @var \Drupal\migrate\Plugin\MigrationPluginManagerInterface
   */
  protected $migrationPluginManager;

  /**
   * @var Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * FsaEstablishmentEntityPurger constructor.
   *
   * @param array $configuration
   *   Configuration.
   * @param string $plugin_id
   *   Plugin ID.
   * @param mixed $plugin_definition
   *   Plugin definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity type manager.
   * @param \Drupal\migrate\Plugin\MigrationPluginManagerInterface $migration_plugin_manager
   *   Migration plugin manager.
   * @param \Drupal\Core\Database\Connection $database
   *   Database connection.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager, MigrationPluginManagerInterface $migration_plugin_manager, Connection $database) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entity_type_manager;
    $this->migrationPluginManager = $migration_plugin_manager;
    $this->database = $database;
  }

  /**
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   DI container.
   * @param array $configuration
   *   Config.
   * @param string $plugin_id
   *   Plugin ID.
   * @param mixed $plugin_definition
   *   Plguin definition.
   *
   * @return \Drupal\Core\Plugin\ContainerFactoryPluginInterface|\Drupal\fsa_ratings_import\Plugin\QueueWorker\FsaEstablishmentEntityPurger
   *   DI runtime values for class constructor to use.
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

  /**
   * @param string $migration_plugin_id
   *   Migration plugin machine name.
   *
   * @return array
   *   Migration table map.
   */
  protected function getMigrationTables($migration_plugin_id) {
    return [
      'map' => 'migrate_map_' . $migration_plugin_id,
      'message' => 'migrate_message_' . $migration_plugin_id,
    ];
  }

  /**
   * Purges obsolete entities.
   *
   * @param mixed $data
   *   Data to process.
   */
  public function processItem($data) {
    /** @var \Drupal\Core\Entity\ContentEntityStorageInterface $storage */
    $storage = $this->entityTypeManager->getStorage($data->entity_type_id);
    // Get migration tables.
    $migration_tables = $this->getMigrationTables($data->migration_plugin_id);
    $map_table = $migration_tables['map'];
    $message_table = $migration_tables['message'];

    // Load all entities with one go.
    $entities = $storage->loadMultiple(array_keys($data->ids));

    foreach ($entities as $entity_id => $entity) {
      // Get entity language.
      $entity_langcode = $data->ids[$entity_id];

      // Remove the entity.
      $entity->delete();

      // In order to clean the migration map, a specific row in mapping table
      // is located by entity destination ID and language code, then manually
      // removed from migration map and message table.
      // The reason for manual process is that
      // invoking migration methods for this purpose is rather slow, but the
      // end result is the same (migration events are not fired, though).

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
