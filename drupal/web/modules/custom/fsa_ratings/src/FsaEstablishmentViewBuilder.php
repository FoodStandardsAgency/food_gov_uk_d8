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

    // "What do the ratings mean" link.
    $url = Url::fromRoute('fsa_ratings.ratings_meanings');
    $url->setOptions(['query' => ['fhrsid' => $entity->id()]]);
    $build['#find_more_link_ratings'] = Link::fromTextAndUrl(t('What do the different ratings mean'), $url )->toString();

    // "What is FHRS" link.
    // @todo: For now link to existing site, correct the link once we have the business guidance page in place.
    $url = Url::fromUri('https://www.food.gov.uk/business-industry/hygieneratings');
    $build['#find_more_link_fhrs'] = Link::fromTextAndUrl(t('What is the Food Hygiene Rating Scheme?'), $url )->toString();

    // "Get badges" link.
    $url = Url::fromUri('https://fhrs-online-display.food.gov.uk/confirm_business/?id=' . $entity->id());
    $url->setOptions(['attributes' => ['class' => ['call-to-action']]]);
    $build['#rating_badge_cta'] = Link::fromTextAndUrl(t('Get badges'), $url)->toString();

    // "Back to search" link with query params.
    $url = Url::fromRoute('fsa_ratings.ratings_search');
    $url->setOptions(['query' => \Drupal::request()->query->all()]);
    $build['#backlink'] = Link::fromTextAndUrl($this->t('Back to ratings search'), $url)->toString();

    return $build;
  }

}
