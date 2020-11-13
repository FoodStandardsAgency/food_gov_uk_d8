<?php
/**
 * @file
 *
 * Drush commands for the fsa_research module.
 */

/**
 * Implements hook_drush_command().
 */
function fsa_research_drush_command() {
  $items = [];

  $items['fsa-research-populate-evidence-type'] = [
    'description' => 'Populates fsa_research_evidence_type queue with nodes.',
    'aliases' => ['fsar-populate'],
  ];

  return $items;
}

/**
 * Populates fsa_research_evidence_type queue with nodes.
 */
function drush_fsa_research_populate_evidence_type() {
  // Check that the Evidence Type field exists on Research project bundle.
  $fields = \Drupal::service('entity_field.manager')
    ->getFieldDefinitions('node', 'research_project');

  if (!isset($fields['field_evidence_type'])) {
    return drush_set_error('error', 'Research Project - Evidence type field does not exist.', 'ERROR: ');
  }

  // Make sure the "Research project" taxonomy term exists.
  $tid = \Drupal::entityQuery('taxonomy_term')
    ->condition('vid','evidence_type')
    ->condition('name','Research project')
    ->execute();

  // Create "Research project" taxonomy term if not already in the vocab.
  if (empty($tid)) {
    $values = [
      'name' => 'Research project',
      'vid' => 'evidence_type'
    ];
    $term = \Drupal::entityTypeManager()->getStorage('taxonomy_term')
                                        ->create($values);
    $term->addTranslation('cy', [
      'name' => 'Prosiect ymchwil',
    ]);

    $term->save();
  }
  
  // Instantiate our research queue.
  $queue = \Drupal::queue('fsa_research_evidence_type');
  $queue->createQueue();

  // Create a queue item for each research project node to be processed.
  $nids = \Drupal::entityQuery('node')
    ->condition('type','research_project')
    ->execute();

  foreach ($nids as $nid) {
    $queue->createItem($nid);
  }

  drush_print(count($nids) . ' Research projects added to the Evidence type queue.');

  return TRUE;
}
