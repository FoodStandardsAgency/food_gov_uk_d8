<?php

namespace Drupal\fsa_ratings_import;

use Drupal\Core\Database\Connection;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\KeyValueStore\KeyValueFactoryInterface;
use Drupal\Core\Queue\QueueFactory;
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

  /**
   * @var string
   */
  protected $establishmentEntityType = 'fsa_establishment';

  /**
   * @var int
   */
  protected $batchSize = 30;

  /**
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected $fileSystem;

  /**
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * @var \Drupal\migrate\Plugin\MigrationPluginManagerInterface
   */
  protected $migrationPluginManager;

  /**
   * @var \Drupal\Core\Queue\QueueInterface
   */
  protected $obsoleteEntityPurgerQueue;

  /**
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * FhrsApiImportMapper constructor.
   *
   * @param \Drupal\Core\Database\Connection $database
   *   Db connection object.
   * @param \Drupal\Core\File\FileSystemInterface $file_system
   *   File system object.
   * @param \Drupal\Core\Datetime\DateFormatterInterface $date_formatter
   *   Date formatter.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity type manager.
   * @param \Drupal\migrate\Plugin\MigrationPluginManagerInterface $migration_plugin_manager
   *   Migration plugin manager.
   * @param \Drupal\Core\Queue\QueueFactory $queue_factory
   *   Queue factory.
   * @param Drupal\Core\KeyValueStore\KeyValueFactoryInterface $key_value_factory
   *   Key value factory.
   * @param \Psr\Log\LoggerInterface $logger
   *   Logger.
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
   *   Table name
   */
  public function getTablename() {
    return self::MAP_TABLE;
  }

  /**
   * Marks the finish of entity purging for the day.
   *
   * @return mixed
   *   Last finish date or null.
   */
  public function finish() {
    return $this->keyValueStorage->set('entity_purge_finish_last_date', $this->getDateValue('Y-m-d'));
  }

  /**
   * Returns TRUE if entity purging is finished for the day.
   *
   * @return bool
   *   Whether finished or not.
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
   * @param string $pattern
   *   Date pattern.
   * @param int $timestamp
   *   UNIX timestamp.
   *
   * @return string
   *   Formatted date string.
   */
  public function getDateValue($pattern = 'Y-m-d', int $timestamp = NULL) {
    $timestamp = $timestamp ? $timestamp : time();

    return $this->dateFormatter->format($timestamp, 'custom', $pattern);
  }

  /**
   * Saves establishment ID
   *
   * @param string $filename
   *   Filename.
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
          'omitted_count' => count(array_filter($results, function ($item) {
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
   *   Migration interface(s).
   */
  public function getMigrations() {
    static $tables = NULL;

    if (is_null($tables)) {
      $tables = [];

      foreach (self::FHRS_MIGRATIONS as $migration_plugin_id) {
        $tables[$migration_plugin_id] = 'migrate_map_' . $migration_plugin_id;
      }
    }

    return $tables;
  }

  /**
   * Removes the entities which are not found in API provided result-set.
   */
  public function rollback() {
    if (!$this->isFinished() && $this->obsoleteEntityPurgerQueue->numberOfItems() == 0) {
      foreach ($this->getMigrations() as $migration_plugin_id => $map_table) {
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
          $data->entity_type_id = $this->establishmentEntityType;
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
