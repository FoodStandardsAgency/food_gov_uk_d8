<?php

namespace Drupal\fsa_notify\Plugin\QueueWorker;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Queue\QueueWorkerBase;
use Drupal\fsa_notify\FsaNotifyStorage;
use Drupal\fsa_notify\FsaNotifyStorageDBConnection;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * An Alert Storage that saves alerts to specific users.
 *
 * @QueueWorker(
 *   id = "fsa_notify_store",
 *   title = @Translation("FSA Notify Store"),
 * )
 */
class FsaNotifyStorageQueue extends QueueWorkerBase implements ContainerFactoryPluginInterface {

  protected $nodeStorage;
  protected $notifyStorage;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    EntityStorageInterface $node_storage,
    FsaNotifyStorageDBConnection $notify_storage
  ) {
    $this->nodeStorage = $node_storage;
    $this->notifyStorage = $notify_storage;
  }

  /**
   * Creates the new queue worker. Change the second parameter to
   * either FsaNotifyStorage to User::load (very memory intensive)
   * or FsaNotifyStorageDB to use database connection directly.
   */
  public static function create(
    ContainerInterface $container,
    array $configuration,
    $plugin_id,
    $plugin_definition
  ) {
    return new static(
      $container->get('entity_type.manager')->getStorage('node'),
      new FsaNotifyStorageDBConnection()
    );
  }

  /**
   * Todo: document.
   */
  public function processItem($data) {

    $node = $this->nodeStorage->load($data['nid']);

    if (empty($node)) {
      return;
    }

    // time-consuming.
    $this->notifyStorage->store($node, $data['lang']);

  }

}
