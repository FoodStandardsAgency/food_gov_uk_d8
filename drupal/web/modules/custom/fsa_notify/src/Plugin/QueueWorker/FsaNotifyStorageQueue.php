<?php

namespace Drupal\fsa_notify\Plugin\QueueWorker;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Queue\QueueWorkerBase;
use Drupal\fsa_notify\FsaNotifyStorage;
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
   * Todo: document.
   */
  public function __construct(
    EntityStorageInterface $node_storage,
    FsaNotifyStorage $notify_storage
  ) {
    $this->nodeStorage = $node_storage;
    $this->notifyStorage = $notify_storage;
  }

  /**
   * Todo: document.
   */
  public static function create(
    ContainerInterface $container,
    array $configuration,
    $plugin_id,
    $plugin_definition
  ) {
    return new static(
      $container->get('entity.manager')->getStorage('node'),
      new FsaNotifyStorage()
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
