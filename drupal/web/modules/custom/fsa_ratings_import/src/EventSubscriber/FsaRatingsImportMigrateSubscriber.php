<?php

namespace Drupal\fsa_ratings_import\EventSubscriber;

use Drupal\Component\Utility\Timer;
use Drupal\migrate\Event\MigrateEvents;
use Drupal\migrate\Event\MigrateImportEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class FsaRatingsImportMigrateSubscriber.
 *
 * @package Drupal\fsa_ratings_import
 */
class FsaRatingsImportMigrateSubscriber implements EventSubscriberInterface {

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
      MigrateEvents::PRE_IMPORT => 'onMigratePreImport',
      MigrateEvents::POST_IMPORT => 'onMigratePostImport',
    ];
  }

  /**
   * Pre import event.
   *
   * @param \Drupal\migrate\Event\MigrateImportEvent $event
   *   The import event.
   */
  public function onMigratePreImport(MigrateImportEvent $event) {

    // @todo: temporary timer start to track excecution times.
    $timername = $event->getMigration()->id();
    Timer::start($timername);
  }

  /**
   * Post import event.
   *
   * @param \Drupal\migrate\Event\MigrateImportEvent $event
   *   The import event.
   */
  public function onMigratePostImport(MigrateImportEvent $event) {

    // @todo: temporary timer printout/stop to track excecution times.
    $timername = $event->getMigration()->id();
    drush_print('Migration ' . $timername . ' took ' . floor(Timer::read($timername) / 1000) . ' seconds to execute');
    Timer::stop($timername);
  }

}
