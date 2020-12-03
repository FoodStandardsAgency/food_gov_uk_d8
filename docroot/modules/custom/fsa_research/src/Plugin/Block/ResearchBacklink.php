<?php

namespace Drupal\fsa_research\Plugin\Block;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Link;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;

/**
 * Provides a 'ResearchBacklink' block.
 *
 * @Block(
 *  id = "research_backlink",
 *  admin_label = @Translation("Research page backlink"),
 * )
 */
class ResearchBacklink extends BlockBase {

  // Content types to show the backlink.
  const CONTENT_TYPES_TO_ENABLE = [
    'research_project',
  ];

  /**
   * {@inheritdoc}
   */
  public function build() {
    global $base_url;
    $build = [];
    $node = \Drupal::routeMatch()->getParameter('node');
    $text = '';
    $options = ['attributes' => ['class' => 'back']];
    if (is_object($node) && in_array($node->getType(), self::CONTENT_TYPES_TO_ENABLE)) {
      // Link research nodes back to search.
      // @todo: Get path from route.
      $path = '/search/research-projects';
      $text = $this->t('Back to search');
    }
    else {
      // Print simple javascript history back link (using the fsa_custom
      // attached jquery class.
      $path = '#';
      $text = $this->t('Back');
      $options['attributes']['class'] = ['back', 'history-back'];
    }

    if ($path) {
      $url = Url::fromUserInput($path, $options);

      // Link to News & Alerts listing page.
      $build['backlink'] = [
        '#markup' => Link::fromTextAndUrl($text, $url)
          ->toString(),
      ];
    }

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  protected function blockAccess(AccountInterface $account) {

    $vid = \Drupal::routeMatch()->getParameter('taxonomy_term');
    $node = \Drupal::routeMatch()->getParameter('node');

    // Display block only if research programme vocabulary or enabled node type.
    if ((isset($vid) && $vid->getVocabularyId() == 'research_programme') ||
        (is_object($node) && in_array($node->getType(), self::CONTENT_TYPES_TO_ENABLE))) {
      return AccessResult::allowed();
    }
    else {
      return AccessResult::forbidden();
    }
  }

}
