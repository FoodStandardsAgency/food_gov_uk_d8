<?php

namespace Drupal\fsa_custom\Plugin\Block;

use Drupal\block\Entity\Block;
use Drupal\Component\Render\FormattableMarkup;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Render\Markup;
use Drupal\Core\Session\AccountInterface;
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

  const CONTENT_TYPES_TO_HIDE = ['help', 'lander', 'webform'];

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];
    $content = [];
    $route = \Drupal::routeMatch();

    $parameters = ['media', 'node', 'taxonomy_term'];
    foreach ($parameters as $parameter) {
      // Skip if this was not the entity we are looking for.
      if (($entity = $route->getParameter($parameter)) == NULL || !is_object($entity)) {
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

      if ($parameter == 'node' && in_array($entity->getType(), ['news', 'alert'])) {
        // News and alerts should display their created date.
        $date = \Drupal::service('date.formatter')->format($entity->getCreatedTime(), 'medium');
      }
      elseif ($parameter == 'media' && $entity->bundle() == 'document') {
        // Get the file last update timestamp.
        $uri = $entity->field_document->entity->getFileUri();
        $stream_wrapper_manager = \Drupal::service('stream_wrapper_manager')->getViaUri($uri);
        $file_path = $stream_wrapper_manager->realpath();
        if (file_exists($file_path)) {
          $timestamp = filemtime($file_path);
        }
        else {
          // Fallback to entity changed time.
          $timestamp = $entity->getChangedTime();
        }
        $date = $this->t('Last updated: @date', ['@date' => \Drupal::service('date.formatter')->format($timestamp, 'medium')]);
      }
      elseif (isset($entity->field_update_date->value)) {
        // Last updated with inlined label.
        $date = $entity->field_update_date->view(['label' => 'inline']);
      }
      else {
        $date = NULL;
      }

      // Set rules when to display print/share links and buttons.
      if ($entity_type == 'node' && !in_array($entity->getType(), self::CONTENT_TYPES_TO_HIDE)) {
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
        $link_print = $this->t('Print this page');

        // The pdf export (with entity_print).
        $route_params = [
          'entity_type' => $entity_type,
          'entity_id' => $entity->id(),
          'export_type' => 'pdf',
        ];
        $url = Url::fromRoute('entity_print.view', $route_params);
        $markup = new FormattableMarkup(
          '<span class="visuallyhidden"> @node_title</span>',
          [
            '@node_title' => str_replace(':', '', $entity->label()),
          ]);
        $link_pdf = [
          '#type' => 'link',
          '#attributes' => [
            'class' => 'print__link--pdf',
            'target' => '_blank',
          ],
          '#title' => $this->t('View @title as PDF', ['@title' => $markup]),
          '#url' => $url,
        ];
      }
      else {
        $link_pdf = NULL;
        $link_print = NULL;
      }

      if ($share) {
        $block = Block::load('addtoany');
        if (is_object($block)) {
          $share = \Drupal::entityTypeManager()->getViewBuilder('block')->view($block);
        }
        else {
          $share = '';
        }
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

  /**
   * {@inheritdoc}
   */
  protected function blockAccess(AccountInterface $account) {

    // Prevent empty block being placed on a pages where content would anyway be
    // empty.
    $node = \Drupal::routeMatch()->getParameter('node');
    if (is_object($node) && in_array($node->getType(), self::CONTENT_TYPES_TO_HIDE)) {
      return AccessResult::forbidden();
    }
    else {
      return AccessResult::allowed();
    }
  }

}
