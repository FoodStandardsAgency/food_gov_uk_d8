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
   * @param string $date
   *    String in datetime format.
   *
   * @param string $format
   *    Drupal date format to format the date.
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup|false|int
   */
  public static function ratingsDate($date, $format = 'medium') {

    $date = strtotime($date);
    if ($date > -1893456000) {
      // Format a nice date display if it is after year 1910.
      $date = \Drupal::service('date.formatter')->format($date, $format);
    }
    else {
      // Return "not available" label for years from the beginning of century.
      $date = t('N/A');
    }

    return $date;
  }

  /**
   * Helper function to get entity details from the FSA ratings system
   * (or any Drupal entity).
   *
   * @param $entity_name
   *    The machine name of the entity.
   * @param $id
   *    The entity id.
   * @param $field
   *    The name of the field or property to get.
   *
   * @return string
   *    Field or property value.
   */
  public static function getEntityDetail($entity_name, $id, $field) {

    $value = '';
    $entity = \Drupal::entityTypeManager()->getStorage($entity_name)->load($id);

    if (is_object($entity)) {
      if ($field == 'name') {
        $value = $entity->getName();
      }
      else if ($entity->hasField($field)) {
        $value = $entity->get($field);
        // @todo: consider handling fields with multiple values?
        $value = $value->first()->getValue()['value'];
      }
    }

    return $value;
  }

  /**
   * Display locally hosted ratings badge image.
   *
   * @param int $rating
   *   Establishment rating value.
   * @param string $scheme
   *    Rating scheme.
   * @param int $embed_type
   *   Embed type code (1|3|4), define the FHRS badge type.
   *
   * @return array
   *   Rating image badge #markup.
   */
  public static function ratingBadgeImageDisplay($rating, $scheme = 'FHRS', $embed_type = 4) {

    // Language name to image path.
    $lang = \Drupal::languageManager()->getCurrentLanguage()->getId();
    switch ($lang) {
      case 'cy':
        $language = 'welsh';
        break;

      default:
        $language = 'english';
        break;
    }

    $badge_path = '';

    // Build the badge based on scheme.
    if ($scheme == 'FHRS') {

      // Not all FHRS rating values match the badge name, rewrite.
      switch ($rating) {
        case 'AwaitingInspection':
        case 'AwaitingPublication':
          $rating = 'pending';
          break;
      }

      $badge_path = $scheme .  '/' . $rating . '/' . 'score-' . $rating . '-' . $embed_type . '-' . $language . '.png';

    }
    else if ($scheme == 'FHIS') {
      $filter = [
        ' ' => '_',
      ];
      $filename = Html::cleanCssIdentifier('fhis_' . $rating, $filter);
      $badge_path = $scheme .  '/' . $filename  . '.jpg';
    }

    if ($badge_path != '') {
      $image_path = '/' . drupal_get_path('module', 'fsa_ratings') . '/images/badge/' . $badge_path;
      $badge = '<img src="' . $image_path . '" alt="FHRS Rating score: ' . $rating . '" />';
    }
    else {
      // In case we could not create a badge_path.
      $badge = '<pre>' . t('Rating badge not available.') . '</pre>';
    }

    return [
      '#markup' => '<div class="badge ratingkey">' . $badge . '</div>',
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
