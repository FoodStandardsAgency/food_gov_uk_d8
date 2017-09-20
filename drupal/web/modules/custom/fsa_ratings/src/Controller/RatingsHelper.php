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

    // Non-numeric FHRS badges.
    $literals = ['exempt', 'pending'];

    // FHIS badges
    $fhis_badges = ['AwaitingInspection', 'AwaitingPublication', 'Improvement Required', 'Pass', 'Pass and Eat Safe'];

    if (is_numeric($rating) || in_array(strtolower($rating), $literals)) {
      // FHRS numeric and few literal ratings as badges.
      $image_path = '/' . drupal_get_path('module', 'fsa_ratings') . '/images/badges/score-' . $rating . '-' . $embed_type . '-' . $language . '.png';
    }
    else if (in_array($rating, $fhis_badges)) {
      // FHIS badges as defined in $fhis_badges variable.
      // Convert to proper image filenames, regexp "CamelCase" -> "camel_case".
      $imgname = strtolower(str_replace(' ', '_', preg_replace('/(?<!^)[A-Z]/', '_$0', $rating)));
      $image_path = '/' . drupal_get_path('module', 'fsa_ratings') . '/images/badges/fhis_' . $imgname . '.png';
    }
    else {
      // Fall back to fetching arbitrary badges from ratings.food.gov.uk.
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
