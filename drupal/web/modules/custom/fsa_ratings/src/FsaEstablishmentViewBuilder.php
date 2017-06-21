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

    // Establishment ratings badge link.
    $url = Url::fromUri('http://api');
    $badge_cta = Link::fromTextAndUrl(t('Get the badges'), $url);
    http://widget.ratings.food.gov.uk/?FHRSID=616044&Culture=en-GB

    $build['#rating_badge_cta'] = $content;

    return $build;
  }

}
