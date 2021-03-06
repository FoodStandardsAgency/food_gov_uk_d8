<?php

namespace Drupal\fsa_ratings;

use Drupal\Component\Utility\Html;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityViewBuilder;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\fsa_ratings\Controller\RatingsHelper;

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

    $lang = \Drupal::languageManager()->getCurrentLanguage()->getId();

    // Include entity ID.
    $build['#id'] = $entity->id();

    // Include canonical URL to entity.
    $build['#url'] = $entity->toUrl();

    // Send entity to custom template.
    $build['#theme'] = 'fsa_establishment';

    // Include used view mode.
    $build['#view_mode'] = $view_mode;

    // Pass the search form for Establishment template.
    $build['#search_form'] = \Drupal::formBuilder()->getForm('Drupal\fsa_ratings\Form\FsaRatingsSearchForm');

    $fields = $entity->getFields();
    $content = [];
    // Loop through fields for overriding how fields are set in display mode.
    foreach ($fields as $field) {
      $content[$field->getFieldDefinition()->getName()] = $field->view($view_mode);
    }
    $build['#content'] = $content;

    // Rating value in textual format.
    $rating_value = $entity->get('field_ratingvalue')->getString();
    if (!empty($rating_value)) {
      $build['#ratingvalue_id'] = Html::cleanCssIdentifier(strtolower($rating_value));

      $build['#rating_value'] = [
        '#markup' => '<p class="ratingvalue"><span class="description">' . $this->t('FHRS Rating score:') . '</span> <span class="numeric">' . $rating_value . '</span></p>',
      ];
    }

    // Build the badge for rating.
    $build['#rating_badge'] = RatingsHelper::ratingBadgeImageDisplay($rating_value, $entity->get('field_schemetype')->getString());

    // "What do the ratings mean" link.
    $url = Url::fromRoute('fsa_ratings.ratings_meanings');
    // Get query to preserve id for backlink and the rating form submission.
    $query = \Drupal::request()->query->all();
    $query['fhrsid'] = $entity->id();
    $url->setOptions(['query' => $query]);
    $build['#find_more_link_ratings'] = Link::fromTextAndUrl(t('What do the different ratings mean'), $url)->toString();

    // "What is FHRS" link.
    // @todo: Add node id via config form (route "fsa_custom.settings")
    $url = Url::fromRoute('entity.node.canonical', ['node' => 165]);
    $build['#find_more_link_fhrs'] = Link::fromTextAndUrl(t('What is the Food Hygiene Rating Scheme?'), $url)->toString();

    // "Get badges" link.
    $badge_api_language = ($lang == 'cy') ? 'welsh' : 'english';
    $badge_url = 'https://fhrs-online-display.food.gov.uk/change_lang/?lang=' . $badge_api_language . '&redirect=/confirm_business/?id=';
    $url = Url::fromUri($badge_url . $entity->id());
    $url->setOptions(['attributes' => ['class' => ['call-to-action']]]);
    $build['#rating_badge_cta'] = Link::fromTextAndUrl(t('Get badges'), $url)->toString();

    // "Back to search" link with query params.
    $url = Url::fromRoute('fsa_ratings.ratings_search');
    $url->setOptions(['query' => \Drupal::request()->query->all(), 'fragment' => RatingsHelper::RESULTS_ANCHOR]);
    $build['#backlink'] = Link::fromTextAndUrl($this->t('Back to ratings search'), $url)->toString();

    return $build;
  }

}
