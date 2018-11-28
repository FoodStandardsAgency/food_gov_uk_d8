<?php

namespace Drupal\fsa_subpages\Plugin\Field\FieldFormatter;

use Drupal\entity_reference_revisions\Plugin\Field\FieldFormatter\EntityReferenceRevisionsEntityFormatter;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;

/**
 * Plugin implementation of the 'entity reference rendered entity' formatter.
 *
 * @FieldFormatter(
 *   id = "entity_reference_revisions_subpages_entity_view",
 *   label = @Translation("Rendered entity for Sub-pages"),
 *   description = @Translation("Display the referenced entities rendered by entity_view() for Sub-pages."),
 *   field_types = {
 *     "entity_reference_revisions"
 *   }
 * )
 */
class EntityReferenceRevisionsSubpagesEntityFormatter extends EntityReferenceRevisionsEntityFormatter implements ContainerFactoryPluginInterface {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {

    $param = \Drupal::request()->query->all();

    $elements = parent::viewElements($items, $langcode);
    foreach ($elements as $key => &$element) {
      $alias = $element['#paragraph']->get('field_url_alias')->getString();
      if (!isset($param[$alias])) {
        unset($elements[$key]);
      }
    }

    $elements['#cache']['contexts'][] = 'url.query_args';
    return $elements;
  }

}
