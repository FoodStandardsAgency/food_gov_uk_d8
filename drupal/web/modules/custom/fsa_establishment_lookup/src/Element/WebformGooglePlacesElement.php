<?php

namespace Drupal\fsa_establishment_lookup\Element;

use Drupal\Core\Render\Element;
use Drupal\Core\Render\Element\FormElement;
use Drupal\Core\Form\FormStateInterface;

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
    ];
  }

  /**
   * Processes a 'webform_googleplace' element.
   */
  public static function processWebformGooglePlacesElement(&$element, FormStateInterface $form_state, &$complete_form) {
    // Attach googleapi external & local js libs.
    $element['#attached']['library'][] = 'fsa_establishment_lookup/googleplaces';
    return $element;
  }

  /**
   * Webform element validation handler for #type 'webform_googleplace'.
   */
  public static function validateWebformGooglePlacesElement(&$element, FormStateInterface $form_state, &$complete_form) {
    // Here you can add custom validation logic.
  }

  /**
   * Prepares a #type 'email_multiple' render element for theme_element().
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
    Element::setAttributes($element, ['id', 'name', 'value', 'size', 'maxlength', 'placeholder']);
    static::setAttributes($element, ['form-text', 'webform-googleplace']);
    return $element;
  }

}
