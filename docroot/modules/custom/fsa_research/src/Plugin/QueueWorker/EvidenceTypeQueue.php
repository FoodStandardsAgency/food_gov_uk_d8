<?php
/**
 * @file
 * Contains \Drupal\fsa_research\Plugin\QueueWorker\EvidenceTypeQueue.
 */

namespace Drupal\fsa_research\Plugin\QueueWorker;

use Drupal\Core\Queue\QueueWorkerBase;

/**
 * Process Research Project node's Evidence type term selection.
 *
 * @QueueWorker(
 *   id = "fsa_research_evidence_type",
 *   title = @Translation("FSA Research Project: Evidence type processor"),
 *   cron = {"time" = 60}
 * )
 */
class EvidenceTypeQueue extends QueueWorkerBase {
  /**
   * {@inheritdoc}
   */
  public function processItem($nid) {
    $node = \Drupal::entityTypeManager()->getStorage('node')->load($nid);

    if (!isset($node)) {
      return;
    }

    // Load taxonomy term ID.
    $tid = \Drupal::entityQuery('taxonomy_term')
              ->condition('vid','evidence_type')
              ->condition('name','Research project')
              ->execute();
    $tid = reset($tid);

    // Update node with evidence type.
    $node->set('field_evidence_type', $tid);
    $node->save();
  }

}