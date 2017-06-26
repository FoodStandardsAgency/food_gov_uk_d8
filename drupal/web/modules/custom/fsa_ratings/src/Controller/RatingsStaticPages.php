<?php

namespace Drupal\fsa_ratings\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\Request;


/**
 * Controller class for the ratings pages.
 *
 * Functions that creates static pages not editable in CMS.
 *
 * @package Drupal\fsa_ratings\Controller
 */
class RatingsStaticPages extends ControllerBase {

  /**
   * Page callback for Ratings meanings page.
   *
   */
  public function ratingMeanings() {

    // Get language to be used in badge.
    $lang = \Drupal::languageManager()->getCurrentLanguage()->getId();

    $ratings_table = [];
    $item_theme = 'fsa_ratings_meanings_item';
    $badge_size = 'medium';

    // Define the rating descriptions for each key.
    $ratings = [
      '5' => $this->t('Top rating. The business is doing well in all three elements (food hygiene, cleanliness of premises and food safety management)'),
      '4' => $this->t('This is an explanation text for this rating.'),
      '3' => $this->t('This is an explanation text for this rating.'),
      '2' => $this->t('This is an explanation text for this rating.'),
      '1' => $this->t('This is an explanation text for this rating.'),
      '0' => $this->t('This is an explanation text for this rating.'),
    ];

    foreach ($ratings as $key => $description) {
      $ratings_table[] = [
        '#theme' => $item_theme,
        '#rating_score' => $key,
        '#rating_badge' => ['#markup' => RatingsHelper::ratingBadge('fhrs_' . $key . '_' . $lang . '-gb', $badge_size)],
        '#rating_description' => $description,
      ];
    }

    return [
      '#theme' => 'fsa_ratings_meanings',
      '#ratings' => $ratings_table,
      '#paragraph_1' => $this->t('The food hygiene rating reflects the hygiene standards found at the time the business is inspected by a food safety officer. These officers are specially trained to assess food hygiene standards.'),
      '#paragraph_2' => $this->t('The rating given shows how well the business is doing overall but also takes account of the element or elements most in need of improving and also the level of risk to people’s health that these issues pose. This is because some businesses will do well in some areas and less well in others but each of the three elements checked is essential for making sure that food hygiene standards meet requirements and the food served or sold to you is safe to eat.'),
    ];
  }

}
