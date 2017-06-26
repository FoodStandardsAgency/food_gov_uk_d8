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

    // Link to "What do the ratings mean" page.
    $url = Url::fromRoute('fsa_ratings.ratings_meanings');
    $link_options = array(
      'query' => [
        'fhrsid' => $entity->id(),
      ],
    );
    $url->setOptions($link_options);
    $build['#find_more_link_ratings'] = Link::fromTextAndUrl(t('What do the different ratings mean'), $url )->toString();

    // Link for the "What is FHRS".
    // @todo: correct the link
    $url = Url::fromUri('http://food.gov.uk');
    $build['#find_more_link_fhrs'] = Link::fromTextAndUrl(t('What is the Food Hygiene Rating Scheme?'), $url )->toString();

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
    $badge_cta = Link::fromTextAndUrl(t('Get badges'), $url )->toString();

    $build['#rating_badge_cta'] = $badge_cta;

    return $build;
  }

}
