<?php

namespace Drupal\linkit_content\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\views\Views;
use Drupal\Component\Utility\Html;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * LinkitContentController controller.
 */
class LinkitContentController extends ControllerBase {

  /**
   * A single page for content selection iframe.
   */
  public function selector() {

    $selected_link = \Drupal::request()->query->get('selected_link');

    // Content has not been selected yet, we need to display content browser.
    if (empty($selected_link)) {

      // Load and render view.
      $view = Views::getView('linkit_content_selector');
      $view->setDisplay('default');
      $view->preExecute();
      $view->execute();

      // Set view as renderable content.
      $content = $view->buildRenderable('default');

      // Attach javascript and css.
      $content['#attached']['library'][] = 'linkit_content/selector';
    }

    // Anchor selection.
    if (!empty($selected_link)) {

      $selected_link_args = explode('/', $selected_link);

      // Let's only deal with nodes for now (not that we're
      // selecting something else yet)
      if ($selected_link_args[1] == 'node') {

        // We will render node instead of loading it, because we don't
        // even know if body field has been replaced with something else.
        // We will get the id's off the html output.
        // Also, there are less chances of html being invalid, and also
        // if element id's are added on the fly, we can get them easily.
        $nid = $selected_link_args[2];
        $entity_type = 'node';
        $view_builder = \Drupal::entityTypeManager()->getViewBuilder($entity_type);
        $storage = \Drupal::entityTypeManager()->getStorage($entity_type);
        $node = $storage->load($nid);
        $uuid = $node->uuid();
        $build = $view_builder->view($node);
        $node_output = render($build);

        // Permission check, deny if user is not supposed to see this content.
        if (!$node->access('view')) {
          throw new AccessDeniedHttpException();
        }

        // This will be anchor list. Current page first option.
        $element_list = [
          [
            'data' => [
              ['data' => $node->getTitle(), 'class' => 'anchor-title'],
              ['data' => '', 'class' => 'anchor-id'],
            ],
            'class' => 'anchor-row',
          ],
        ];

        // Find all h2 tags, extract id and content (title).
        $html_dom = Html::load($node_output);
        $elements = $html_dom->getElementsByTagName('h2');

        if (!empty($elements)) {
          foreach ($elements as $element) {
            $id = $element->getAttribute('id');
            $value = $element->nodeValue;
            if (!empty($id)) {
              $element_list[] = [
                'data' => [
                  ['data' => $value, 'class' => 'anchor-title'],
                  ['data' => '#' . $id, 'class' => 'anchor-id'],
                ],
                'class' => 'anchor-row',
              ];
            }
          }
        }

        // Build the table with anchors.
        $content = [
          '#theme' => 'table',
          '#attributes' => ['class' => ['linkit-content-anchor-table']],
          '#header' => [$this->t('Title'), $this->t('Anchor')],
          '#rows' => $element_list,
          '#attached' => ['library' => ['linkit_content/selector']],
        ];

        $content['#attached']['drupalSettings']['linkit_content']['selected_link'] = $selected_link;
        $content['#attached']['drupalSettings']['linkit_content']['selected_uuid'] = $uuid;
        $content['#attached']['drupalSettings']['linkit_content']['selected_type'] = 'node';
        $content['#attached']['drupalSettings']['linkit_content']['selected_substitution'] = 'canonical';
      }
    }

    return $content;
  }

}
