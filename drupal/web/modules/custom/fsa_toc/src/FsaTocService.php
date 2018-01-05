<?php

namespace Drupal\fsa_toc;

use Drupal\toc_api\Entity\TocType;

/**
 * Class FsaTocService.
 */
class FsaTocService {

  /**
   * Constructs a new FsaTocService object.
   */
  public function __construct() {

  }

  /**
   * Render TOC Anchor menu from html content.
   *
   * @param string $content
   *   HTML representation to get the anchors from.
   * @param string $toc_type
   *   Toc type as stored in /admin/structure/toc.
   *
   * @return false|\Drupal\toc_api\Toc
   *   Renderable table of contents anchor links.
   */
  public function renderAnchors($content = '', $toc_type = 'default') {
    $anchors = FALSE;

    // Get custom TOC type options.
    /** @var \Drupal\toc_api\TocTypeInterface $toc_type */
    $toc_type = TocType::load($toc_type);
    $options = ($toc_type) ? $toc_type->getOptions() : [];

    // Create TOC instance using the TOC manager.
    /** @var \Drupal\toc_api\TocManagerInterface $toc_manager */
    $toc_manager = \Drupal::service('toc_api.manager');
    /** @var \Drupal\toc_api\TocInterface $toc */
    $toc = $toc_manager->create('toc_filter', $content, $options);

    // If provided HTML allows creating the toc build it.
    if ($toc->isVisible()) {
      /** @var \Drupal\toc_api\TocBuilderInterface $toc_builder */
      $toc_builder = \Drupal::service('toc_api.builder');
      $anchors = [
        'toc' => $toc_builder->buildToc($toc),
      ];
    }

    return $anchors;
  }

}
