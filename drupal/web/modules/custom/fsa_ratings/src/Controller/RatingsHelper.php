<?php

namespace Drupal\fsa_ratings\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Controller class for the ratings helper.
 *
 * @package Drupal\fsa_ratings\Controller
 */
class RatingsHelper extends ControllerBase {

  /**
   * Get rating badge from FHRS API.

   * @param string $rating
   *   The rating value.
   * @param string $image_size
   *   Image size (small|medium|large)
   *
   * @return string
   *   Rating image badge (@todo: use drupal image functionality)
   */
  public static function ratingBadge($rating, $image_size = 'medium') {
    $alt = t('Food hygiene Rating score @score', ['@score' => $rating]);
    return '<img src="http://ratings.food.gov.uk/images/scores/' . $image_size . '/' . $rating . '.JPG" alt="' . $alt .'" />';
  }

}
