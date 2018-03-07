<?php

namespace Drupal\fsa_ratings_import;

use Drupal\Core\Database\Connection;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\KeyValueStore\KeyValueFactoryInterface;
use Drupal\Core\Queue\QueueFactory;
use Drupal\migrate\Plugin\MigrationInterface;
use Drupal\migrate\Plugin\MigrationPluginManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Class FhrsApiImportMapper
 */
class FhrsApiImportMapper {

  /** Defines DB table where all results from API are stored. */
  const MAP_TABLE = 'fsa_establishment_api_import';

  /** Defines migrations which imported result-set should be compared against.  */
  const FHRS_MIGRATIONS = ['fsa_establishment', 'fsa_establishment_cy'];

  /** @var int $batchSize */
  protected $batchSize = 30;

  /** @var \Drupal\Core\Database\Connection $database */
  protected $database;

  /** @var \Drupal\Core\File\FileSystemInterface $fileSystem */
  protected $fileSystem;

  /** @var \Drupal\Core\Datetime\DateFormatterInterface $dateFormatter */
  protected $dateFormatter;

  /** @var \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager */
  protected $entityTypeManager;

  /** @var \Drupal\migrate\Plugin\MigrationPluginManagerInterface $migrationPluginManager */
  protected $migrationPluginManager;

  /** @var \Drupal\Core\Queue\QueueInterface $obsoleteEntityPurgerQueue */
  protected $obsoleteEntityPurgerQueue;

  /** @var \Psr\Log\LoggerInterface $logger */
  protected $logger;

  /**
   * FhrsApiImportMapper constructor.
   *
   * @param \Drupal\Core\Database\Connection $database
   * @param \Drupal\Core\File\FileSystemInterface $file_system
   * @param \Drupal\Core\Datetime\DateFormatterInterface $date_formatter
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   * @param \Drupal\migrate\Plugin\MigrationPluginManagerInterface $migration_plugin_manager
   * @param \Drupal\Core\Queue\QueueFactory $queue_factory
   * @param \Psr\Log\LoggerInterface $logger
   */
  public function __construct(Connection $database, FileSystemInterface $file_system, DateFormatterInterface $date_formatter, EntityTypeManagerInterface $entity_type_manager, MigrationPluginManagerInterface $migration_plugin_manager, QueueFactory $queue_factory, KeyValueFactoryInterface $key_value_factory, LoggerInterface $logger) {
    $this->database = $database;
    $this->fileSystem = $file_system;
    $this->dateFormatter = $date_formatter;
    $this->entityTypeManager = $entity_type_manager;
    $this->migrationPluginManager = $migration_plugin_manager;
    $this->obsoleteEntityPurgerQueue = $queue_factory->get('fsa_ratings_import_entity_purger');
    $this->keyValueStorage = $key_value_factory->get('fsa_ratings_import');
    $this->logger = $logger;
  }

  /**
   * Returns mapping table name.
   *
   * @return string
   */
  public function getTablename() {
    return self::MAP_TABLE;
  }

  /**
   * Marks the finish of entity purging for the day.
   *
   * @return mixed
   */
  public function finish() {
    return $this->keyValueStorage->set('entity_purge_finish_last_date', $this->getDateValue('Y-m-d'));
  }

  /**
   * Returns TRUE if entity purging is finished for the day.
   *
   * @return bool
   */
  public function isFinished() {
    $finished_last_day = $this->keyValueStorage->get('entity_purge_finish_last_date');

    return $finished_last_day == $this->getDateValue('Y-m-d');
  }

  /**
   * Clears old entries from map table that (all but today).
   */
  protected function purge() {
    $date = $this->getDateValue();

    $this->database->delete($this->getTablename())
      ->condition('date', $date, '!=')
      ->execute();
  }

  /**
   * Returns date value suitable for use in "date" column in the map table.
   *
   * @param $pattern
   * @param null $timestamp
   *
   * @return string
   */
  public function getDateValue($pattern = 'Y-m-d', $timestamp = NULL) {
    $timestamp = $timestamp ? $timestamp : time();

    return $this->dateFormatter->format($timestamp, 'custom', $pattern);
  }

  /**
   * Saves establishment ID
   *
   * @param $filename
   *
   * @throws \Exception
   */
  public function saveMap($filename) {
    $real_filename = $this->fileSystem->realpath($filename);

    if ($json_string = file_get_contents($real_filename)) {
      $json = json_decode($json_string);

      if (!empty($json->establishments)) {
        $date = $this->getDateValue();
        $results = [];

        foreach ($json->establishments as $establishment) {
          if (isset($establishment->FHRSID)) {
            $result = $this->database->merge($this->getTablename())
              ->key([
                'date' => $date,
                'fhrsid' => $establishment->FHRSID,
              ])
              ->execute();
            $results[] = $result;
          }
        }

        $this->logger->info('Establishments added/updated in table {table}: {count}/{omitted_count}.', [
          'count' => count(array_filter($results)),
          'table' => $this->getTablename(),
          'omitted_count' => count(array_filter($results, function($item) {
            return is_null($item);
          })),
        ]);
      }
      else {
        $this->logger->warning('No establishments found in {filename}.', [
          'filename' => $filename,
        ]);
      }
    }
    else {
      $this->logger->error('Could not read content from {filename}.', [
        'filename' => $filename,
      ]);
    }

    // Purge the DB.
    $this->purge();
  }


  /**
   * Returns migrations.
   *
   * @return \Drupal\migrate\Plugin\MigrationInterface[]
   */
  public function getMigrations() {
    static $tables = NULL;

    if (is_null($tables)) {
      $tables = [];

      foreach (self::FHRS_MIGRATIONS as $migration_plugin_id) {
        $tables[$migration_plugin_id] = $this->migrationPluginManager->createInstance($migration_plugin_id);
      }
    }

    return $tables;
  }

  /**
   * Returns migration map table name.
   *
   * @param \Drupal\migrate\Plugin\MigrationInterface $migration_plugin
   *
   * @return string
   */
  public function getMigrationMapTable(MigrationInterface $migration_plugin) {
    return $migration_plugin->getIdMap()->mapTableName();
  }

  /**
   * Returns the entity type from configuration or plugin ID.
   *
   * Destination plugin has a method that does this but it's protected.
   *
   * @param \Drupal\migrate\Plugin\MigrationInterface $migration_plugin
   *
   * @return string
   */
  public function getEntityTypeId(MigrationInterface $migration_plugin) {
    $plugin_id = $migration_plugin->getDestinationPlugin()->getPluginDefinition()['id'];

    // Remove "entity:".
    return substr($plugin_id, 7);
  }

  /**
   * Removes the entities which are not found in API provided result-set.
   */
  public function rollback() {
    if (!$this->isFinished() && $this->obsoleteEntityPurgerQueue->numberOfItems() == 0) {
      foreach ($this->getMigrations() as $migration_plugin_id => $migration_plugin) {
        $entity_type_id = $this->getEntityTypeId($migration_plugin);
        $map_table = $this->getMigrationMapTable($migration_plugin);

        $query = $this->database->select($map_table, 'm');
        $query->fields('m', ['destid1', 'destid2']);
        $query->leftJoin($this->getTablename(), 'ai', 'm.sourceid1 = ai.fhrsid');
        $query->isNull('ai.fhrsid');

        // Get all obsolete entity IDs.
        $obsolete_entity_ids = $query->execute()->fetchAllKeyed(0, 1);

        // Create a queue item.
        foreach (array_chunk($obsolete_entity_ids, $this->batchSize, TRUE) as $ids) {
          $data = new \stdClass();
          $data->ids = $ids;
          $data->entity_type_id = $entity_type_id;
          $data->migration_plugin_id = $migration_plugin_id;
          $this->obsoleteEntityPurgerQueue->createItem($data);
        }
      }

      // Mark process as finished.
      $this->finish();

      $this->logger->info('Entity purging is finished for {date}', [
        'date' => $this->getDateValue('Y-m-d'),
      ]);
    }
  }

}
