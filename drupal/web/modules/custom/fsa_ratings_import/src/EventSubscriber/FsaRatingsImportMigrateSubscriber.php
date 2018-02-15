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
      // Switch/change the offset for every second import.
      // We do this due to the quite a heavy import process that runs out of
      // memory before getting everything parsed.
      $offset = \Drupal::state()->get(self::RATING_IMPORT_START_OFFSET_VAR);
      switch ($offset) {
        case 1:
          // @todo get the good "halfway" calling the API instead of hardcoded value.
          \Drupal::state()->set(self::RATING_IMPORT_START_OFFSET_VAR, 50);
          break;

        default:
          \Drupal::state()->set(self::RATING_IMPORT_START_OFFSET_VAR, 1);
      }
    }
  }

}
