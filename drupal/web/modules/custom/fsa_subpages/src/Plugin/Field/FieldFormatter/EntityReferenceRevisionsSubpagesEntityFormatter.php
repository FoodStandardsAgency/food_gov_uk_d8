<?php

namespace Drupal\fsa_subpages\Plugin\Field\FieldFormatter;

use Drupal\entity_reference_revisions\Plugin\Field\FieldFormatter\EntityReferenceRevisionsEntityFormatter;
use Drupal\Core\Entity\EntityDisplayRepositoryInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

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

    // subpage value must be:
    // 1 <= subpage <= element_count

    if (empty($param['subpage'])) {
      return [];
    }

    $subpage = $param['subpage'];

    if (!is_numeric($subpage) || $subpage != (int) $subpage) {
      return [];
    }

    $subpage = (int) $subpage;
    $elements = parent::viewElements($items, $langcode);

    if ($subpage < 1 || count($elements) < $subpage) {
      return [];
    }

    $subpage = $subpage - 1;

    $subpage = $elements[$subpage];
    $subpage['#cache']['contexts'][] = 'url.query_args:subpage';
    return [$subpage];
  }

}
