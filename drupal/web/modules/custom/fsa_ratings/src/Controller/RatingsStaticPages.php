<?php

namespace Drupal\fsa_ratings\Controller;

use Drupal\Core\Controller\ControllerBase;
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

    $ratings = [];
    $item_theme = 'fsa_ratings_meanings_item';
    $badge_size = 'medium';

    $score = 5;
    $ratings[0] = [
      '#theme' => $item_theme,
      '#rating_score' => $score,
      '#rating_badge' => ['#markup' => RatingsHelper::ratingBadge('fhrs_' . $score . '_' . $lang .'-gb', $badge_size)],
      '#rating_description' => $this->t('Top rating. The business is doing well in all three elements (food hygiene, cleanliness of premises and food safety management)'),
    ];

    $score = 4;
    $ratings[1] = [
      '#theme' => $item_theme,
      '#rating_score' => $score,
      '#rating_badge' => ['#markup' => RatingsHelper::ratingBadge('fhrs_' . $score . '_' . $lang .'-gb', $badge_size)],
      '#rating_description' => $this->t('lorem ipsum'),
    ];

    $score = 3;
    $ratings[2] = [
      '#theme' => $item_theme,
      '#rating_score' => $score,
      '#rating_badge' => ['#markup' => RatingsHelper::ratingBadge('fhrs_' . $score . '_' . $lang .'-gb', $badge_size)],
      '#rating_description' => $this->t('lorem ipsum'),
    ];

    $score = 2;
    $ratings[3] = [
      '#theme' => $item_theme,
      '#rating_score' => $score,
      '#rating_badge' => ['#markup' => RatingsHelper::ratingBadge('fhrs_' . $score . '_' . $lang .'-gb', $badge_size)],
      '#rating_description' => $this->t('lorem ipsum'),
    ];

    $score = 1;
    $ratings[4] = [
      '#theme' => $item_theme,
      '#rating_score' => $score,
      '#rating_badge' => ['#markup' => RatingsHelper::ratingBadge('fhrs_' . $score . '_' . $lang .'-gb', $badge_size)],
      '#rating_description' => $this->t('lorem ipsum'),
    ];

    $score = 0;
    $ratings[5] = [
      '#theme' => $item_theme,
      '#rating_score' => $score,
      '#rating_badge' => ['#markup' => RatingsHelper::ratingBadge('fhrs_' . $score . '_' . $lang .'-gb', $badge_size)],
      '#rating_description' => $this->t('lorem ipsum'),
    ];

    return [
      '#theme' => 'fsa_ratings_meanings',
      '#ratings' => $ratings,
      '#backlink' => 'link back to establishment page',
    ];
  }

}
