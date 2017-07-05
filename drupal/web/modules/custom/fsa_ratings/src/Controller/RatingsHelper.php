<?php

namespace Drupal\fsa_ratings\Controller;

use Drupal\Component\Utility\Html;
use Drupal\Core\Controller\ControllerBase;

/**
 * Controller class for the ratings helper.
 *
 * @package Drupal\fsa_ratings\Controller
 */
class RatingsHelper extends ControllerBase {

  /**
   * Build a ratings badge (old style, displayed as image).
   *
   * @param string $rating
   *   The rating value.
   * @param string $image_size
   *   Image size (small|medium|large)
   * @param bool $value_only
   *   True to parse the badge from value only.
   *
   * @return array
   *   Rating image badge #markup (@todo: use drupal image functionality)
   */
  public static function ratingBadge($rating, $image_size = 'medium', $value_only = TRUE) {

    // Get language to be used in badge.
    $lang = \Drupal::languageManager()->getCurrentLanguage()->getId();

    if ($value_only) {
      // If value is numeric use FHIS scheme badge.
      $scheme = (is_numeric($rating)) ? 'fhrs' : 'fhis';

      if ($scheme == 'fhis') {
        $filter = [
          ' ' => '_',
          '/' => '_',
          '[' => '_',
          ']' => '_',
        ];
        $rating = Html::cleanCssIdentifier($rating, $filter);
      }
      $ratingkey = $scheme . '_' . $rating . '_' . $lang . '-gb';
    }
    else {
      // If full rating badge name passed.
      $ratingkey = $rating;
    }
    $alt = t('Food hygiene Rating score @score', ['@score' => $rating]);
    return ['#markup' => '<div class="badge ratingkey"><img src="http://ratings.food.gov.uk/images/scores/' . $image_size . '/' . $ratingkey . '.JPG" alt="' . $alt . '" /></div>'];
  }

  /**
   * Display locally hosted ratings badge image.
   *
   * The images are fetched from
   * https://s3-eu-west-1.amazonaws.com/assets.food.gov.uk, offered as
   * downloadables at fhrs-online-display.food.gov.uk).
   *
   * @param int $rating
   *   The establishment fhrsid.
   * @param int $embed_type
   *   Embed type code (1|2|3|4)
   *
   * @return array
   *   Rating image badge #markup as loaded from the API.
   */
  public static function ratingBadgeImageDisplay($rating, $embed_type = 4) {

    $lang = \Drupal::languageManager()->getCurrentLanguage()->getId();

    switch ($lang) {
      case 'cy':
        $language = 'welsh';
        break;

      default:
        $language = 'english';
        break;
    }

    $literals = ['exempt', 'pending'];
    if (is_numeric($rating) || in_array(strtolower($rating), $literals)) {
      // Only numeric, exempt & pending badges are available locally.
      $image_path = '/' . drupal_get_path('module', 'fsa_ratings') . '/images/badges/score-' . $rating . '-' . $embed_type . '-' . $language . '.png';
    }
    else {
      // Fall back to fetching the FHIS badges from ratings.food.gov.uk.
      // @todo: Figure out what badge to display for FHIS businesses.
      $filter = [
        ' ' => '_',
        '/' => '_',
        '[' => '_',
        ']' => '_',
      ];
      $image_path = 'http://ratings.food.gov.uk/images/scores/large/fhis_' . Html::cleanCssIdentifier($rating, $filter) . '_' . $lang . '-gb.JPG';
    }

    return [
      '#markup' => '<div class="badge ratingkey"><img src="' . $image_path . '" alt="FHRS Rating score: ' . $rating . '" /></div>',
    ];
  }

  /**
   * Build a ratings badge (from fhrs-online-display.food.gov.uk).
   *
   * @param int $fhrsid
   *   The establishment fhrsid.
   * @param int $embed_type
   *   Embed type code (1|2|3|4)
   *
   * @return array
   *   Rating image badge #markup as loaded from the API.
   */
  public static function ratingBadgeOnlineDisplay($fhrsid, $embed_type = 4) {

    // @todo: sort out why using this ext js conflicts with Drupal js.

    // Get language to be used in badge.
    $lang = \Drupal::languageManager()->getCurrentLanguage()->getId();

    switch ($lang) {
      case 'cy':
        $language = 'welsh';
        break;

      default:
        $language = 'english';
        break;
    }

    // @todo: move to a template?
    $markup = '
    <div class="fhrs-rating-embed" data-fhrsid="' . $fhrsid . '" data-embed-type="' . $embed_type . '" data-popup-type="popup"></div>
    <script>
      (function() {
        window.FHRSID_DATA = {
            domain: \'fhrs-online-display.food.gov.uk\',
            lang: \'' . $language . '\',
            fhrsid: ' . $fhrsid . '
        }
        if (!document.getElementById("fhrs-rating-javascript")) {
            var d = document, s = d.createElement(\'script\');
            s.src = \'https://fhrs-online-display.food.gov.uk/static/js/fhrs-embed.js\';
            s.setAttribute(\'id\', \'fhrs-rating-javascript\');
            s.setAttribute(\'data-timestamp\', +new Date());
            (d.head || d.body).appendChild(s);
        }

      })();
    </script>
    <noscript>
      <iframe src="https://fhrs-online-display.food.gov.uk/api/v1/generated_assets/' . $fhrsid . '/?embed_type=' . $embed_type . '&lang=' . $language . '" style="border:none;"></iframe>
    </noscript>';

    return [
      '#markup' => $markup,
      '#allowed_tags' => [
        'script', 'noscript', 'iframe', 'div',
      ],
    ];
  }

  /**
   * Unified form cache setting.
   */
  public static function formCacheControl() {
    // Disables form cache to make the form usable for anonymous.
    // @todo: is this really the best way?
    return ['max-age' => 0];
  }

}
