<?php

namespace Drupal\fsa_ratings;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityViewBuilder;
use Drupal\Core\Link;
use Drupal\Core\Url;

/**
 * Extended class for establishment entity view builder.
 *
 * @ingroup entity_api
 */
class FsaEstablishmentViewBuilder extends EntityViewBuilder {

  /**
   * {@inheritdoc}
   */
  public function view(EntityInterface $entity, $view_mode = 'full', $langcode = NULL) {

    // Send entity to custom template.
    $build['#theme'] = 'fsa_establishment';
    $fields = $entity->getFields();
    $content = [];
    // Loop through all fields for overriding how fields are sit in display mode.
    foreach ($fields as $field) {
      if ($field->getFieldDefinition()->getName() == 'name') {
        // Skip adding name since it is already sent for page.
        continue;
      }
      $content[$field->getFieldDefinition()->getName()] = $field->view($view_mode);
    }
    $build['#content'] = $content;

    // Link for the "Get badges".
    // @todo: correct link or get decicion if the page should allow user to copy-paste the widget code as in the old site?
    $url = Url::fromUri('http://widget.ratings.food.gov.uk');
    $link_options = array(
      'attributes' => [
        'class' => [
          'call-to-action',
        ],
      ],
    );
    $url->setOptions($link_options);
    $badge_cta = Link::fromTextAndUrl(t('Link title'), $url )->toString();

    $build['#rating_badge_cta'] = $badge_cta;

    return $build;
  }

}
