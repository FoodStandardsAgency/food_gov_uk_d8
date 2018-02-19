<?php

namespace Drupal\fsa_ratings_import\EventSubscriber;

use Drupal\migrate\Event\MigrateEvents;
use Drupal\migrate\Event\MigrateImportEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class FsaRatingsImportMigrateSubscriber.
 *
 * @package Drupal\fsa_ratings_import
 */
class FsaRatingsImportMigrateSubscriber implements EventSubscriberInterface {

  // The name of the offset state variable.
  const RATING_IMPORT_START_OFFSET_VAR = 'fsa_rating_api_offset';

  /**
   * Constructs MigrationEvents object.
   */
  public function __construct() {

  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      MigrateEvents::POST_IMPORT => 'onMigratePostImport',
    ];
  }

  /**
   * Sets an offset for next establishment migration process.
   *
   * @param \Drupal\migrate\Event\MigrateImportEvent $event
   *   The import event.
   */
  public function onMigratePostImport(MigrateImportEvent $event) {

    // Change the offset only on Establishments import.
    if ($event->getMigration()->id() == 'fsa_establishment') {
      // Increase the offset by one with each iteration,
      // we do this due to the quite a heavy import process that runs out of
      // memory before getting everything parsed.
      // @todo: once memory consumption has tuned to parse more items refactore/remove this "hack".
      $offset = \Drupal::state()->get(self::RATING_IMPORT_START_OFFSET_VAR);
      if (!isset($offset) || $offset >= 102) {
        // Reset to 1 after getting tho current end of pages.
        \Drupal::state()->set(self::RATING_IMPORT_START_OFFSET_VAR, 1);
      }
      else {
        \Drupal::state()->set(self::RATING_IMPORT_START_OFFSET_VAR, $offset + 1);
      }
      drush_print_r($offset);
    }
  }

}
