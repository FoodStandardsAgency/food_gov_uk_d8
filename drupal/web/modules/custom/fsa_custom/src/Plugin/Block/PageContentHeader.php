<?php

namespace Drupal\fsa_custom\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Url;

/**
 * Provides a 'PageContentHeader' block.
 *
 * @Block(
 *  id = "page_content_header",
 *  admin_label = @Translation("Page content header"),
 * )
 */
class PageContentHeader extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];
    $content = [];
    $route = \Drupal::routeMatch();

    $parameters = ['node', 'taxonomy_term'];
    foreach ($parameters as $parameter) {
      // Skip if this was not the entity we are looking for.
      if (($entity = $route->getParameter($parameter)) == NULL) {
        continue;
      }

      // Store the entity type for later use.
      $entity_type = $entity->getEntityType()->id();

      // Entity intro field, no label.
      if (isset($entity->field_intro)) {
        $intro = $entity->get('field_intro')->view(['label' => 'hidden']);
      }
      else {
        $intro = NULL;
      }

      // Last updated with inlined label.
      if (isset($entity->field_update_date->value)) {
        $date = $entity->field_update_date->view(['label' => 'inline']);
      }
      else {
        $date = NULL;
      }

      // Set rules when to display print/share links and buttons.
      if ($entity_type == 'node' && !in_array($entity->getType(), ['help', 'lander'])) {
        $print_actions = TRUE;
        $share = TRUE;
      }
      elseif ($entity_type == 'taxonomy_term' && $entity->getVocabularyId() == 'research_programme') {
        // Limit to research programmes.
        $print_actions = TRUE;
        $share = TRUE;
      }
      else {
        $print_actions = FALSE;
        $share = FALSE;
      }

      if ($print_actions) {
        // @todo: The print link (attach a js file to module).
        $link_print = $this->t('Print this page');

        // The pdf export (with entity_print).
        $route_params = [
          'entity_type' => $entity_type,
          'entity_id' => $entity->id(),
          'export_type' => 'pdf',
        ];
        $url = Url::fromRoute('entity_print.view', $route_params);
        $link_pdf = [
          '#type' => 'link',
          '#prefix' => '<div>',
          '#suffix' => '</div>',
          '#attributes' => ['class' => 'print__link--pdf'],
          '#title' => $this->t('View PDF'),
          '#url' => $url,
        ];
      }
      else {
        $link_pdf = NULL;
        $link_print = NULL;
      }

      if ($share) {
        // @todo: FSA-571 to implement.
        $share = ['#markup' => '<div>' . $this->t('Share') . '</div>'];
      }

      $attributes = ['class' => 'page-content-header'];

      $build['page_content_header'] = [
        '#theme' => 'fsa_content_header',
        '#attributes' => $attributes,
        '#intro' => $intro,
        '#update_date' => $date,
        '#link_pdf' => $link_pdf,
        '#link_print' => $link_print,
        '#share' => $share,
      ];
    }

    return $build;
  }

}
