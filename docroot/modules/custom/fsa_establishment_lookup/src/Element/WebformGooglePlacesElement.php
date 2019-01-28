<?php

namespace Drupal\fsa_establishment_lookup\Element;

use Drupal\Core\Render\Element;
use Drupal\Core\Render\Element\FormElement;
use Drupal\Core\Form\FormStateInterface;
use Drupal\fsa_ratings\Controller\RatingsHelper;

/**
 * Provides a 'webform_googleplace'.
 *
 * Webform elements are just wrappers around form elements, therefore every
 * webform element must have correspond FormElement.
 *
 * Below is the definition for a custom 'webform_googleplace' which just
 * renders a simple text field.
 *
 * @FormElement("webform_googleplace")
 *
 * @see \Drupal\Core\Render\Element\FormElement
 * @see https://api.drupal.org/api/drupal/core%21lib%21Drupal%21Core%21Render%21Element%21FormElement.php/class/FormElement
 * @see \Drupal\Core\Render\Element\RenderElement
 * @see https://api.drupal.org/api/drupal/namespace/Drupal%21Core%21Render%21Element
 * @see \Drupal\fsa_establishment_lookup\Element\WebformGooglePlacesElement
 */
class WebformGooglePlacesElement extends FormElement {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    $class = get_class($this);
    return [
      '#input' => TRUE,
      '#size' => 60,
      '#process' => [
        [$class, 'processWebformGooglePlacesElement'],
      ],
      '#element_validate' => [
        [$class, 'validateWebformGooglePlacesElement'],
      ],
      '#pre_render' => [
        [$class, 'preRenderWebformGooglePlacesElement'],
      ],
      '#theme' => 'input__webform_googleplaces',
      '#theme_wrappers' => ['form_element'],
      // GDS requires no placeholders.
      '#placeholder' => '',
    ];
  }

  /**
   * Processes a 'webform_googleplace' element.
   */
  public static function processWebformGooglePlacesElement(&$element, FormStateInterface $form_state, &$complete_form) {
    // Attach googleapi external & local js libs.
    $element['#attached']['library'][] = 'fsa_establishment_lookup/googleplaces';
    $element['#attached']['drupalSettings']['fsa_establishment_lookup']['googleplaces']['element_id'] = $element['#id'];
    return $element;
  }

  /**
   * Webform element validation handler for #type 'webform_googleplace'.
   */
  public static function validateWebformGooglePlacesElement(&$element, FormStateInterface $form_state, &$complete_form) {

    if ($element['#type'] == 'webform_googleplace') {

      $fsa_authority = NULL;
      $fsa_authority_id = 0;

      // Postcode value is sourced from Google Places, populated into the form element via JS.
      $postcode = $form_state->getValue('fsa_establishment_postal_code');

      // If for any reason the postcode is empty try matching on raw input values with regex to extract from there.
      if (empty($postcode)) {
        $location_text = $form_state->getValue('where_lookup');

        // Regex sourced from https://andrewwburns.com/2018/04/10/uk-postcode-validation-regex/.
        $uk_postcode_regex = '(([A-Z][0-9]{1,2})|(([A-Z][A-HJ-Y][0-9]{1,2})|(([A-Z][0-9][A-Z])|([A-Z][A-HJ-Y][0-9]?[A-Z])))) [0-9][A-Z]{2}';

        $matches = [];
        preg_match_all("/$uk_postcode_regex/", $location_text, $matches);

        if (!empty($matches[0])) {
          $postcode = reset($matches[0]);
        }
      }

      // Add the local authority on validate.
      if (!empty($postcode)) {
        // Lookup the local authority for the establishment by using the postcode value supplied.
        $mapit_service_data = \Drupal::service('fsa_team_finder.get_local_authority')->get($postcode);

        // Entity query fsa local authority.
        $fsa_authority = \Drupal::entityTypeManager()
          ->getStorage('fsa_authority')
          ->loadByProperties([
            'field_mapit_area' => $mapit_service_data['mapit_area'],
          ]);

        if (!empty($fsa_authority)) {
          // Take first result only, just in case there is > 1 authority with that MapIt area.
          $fsa_authority = reset($fsa_authority);
          $fsa_authority_id = $fsa_authority->id();
        }

        if (!empty($fsa_authority_id)) {
          $form_state->setValue('fsa_establishment_la', $fsa_authority_id);
          $form_state->setValue('fsa_establishment_la_name', RatingsHelper::getEntityDetail('fsa_authority', $fsa_authority_id, 'name'));

          // Ensure admins can save local authority emails after submission.
          $route = \Drupal::routeMatch()->getRouteName();
          $routes = [
            'entity.webform_submission.edit_form',
            'entity.webform_submission.edit_form.all',
          ];

          if (!in_array($route, $routes)) {
            $form_state->setValue('fsa_establishment_la_email', RatingsHelper::getEntityDetail('fsa_authority', $fsa_authority_id, 'field_email'));
          }

          $form_state->setValue('fsa_establishment_la_email_overridden', RatingsHelper::getEntityDetail('fsa_authority', $fsa_authority_id, 'field_email_overridden'));
          $form_state->setValue('fsa_establishment_la_email_alt', RatingsHelper::getEntityDetail('fsa_authority', $fsa_authority_id, 'field_email_alt'));
        }
      }

    }
  }

  /**
   * Prepares a #type 'text' render element for theme_element().
   *
   * @param array $element
   *   An associative array containing the properties of the element.
   *   Properties used: #title, #value, #description, #size, #maxlength,
   *   #placeholder, #required, #attributes.
   *
   * @return array
   *   The $element with prepared variables ready for theme_element().
   */
  public static function preRenderWebformGooglePlacesElement(array $element) {
    $element['#attributes']['type'] = 'text';
    Element::setAttributes($element, [
      'id',
      'name',
      'value',
      'size',
      'maxlength',
      'placeholder',
    ]
    );
    static::setAttributes($element, ['form-text', 'webform-googleplace']);
    return $element;
  }

}
