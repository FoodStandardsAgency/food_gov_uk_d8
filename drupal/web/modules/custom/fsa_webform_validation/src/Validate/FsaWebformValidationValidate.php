<?php

namespace Drupal\fsa_webform_validation\Validate;

use Drupal\Core\Form\FormStateInterface;

/**
 * Validates webform elements.
 */
class FsaWebformValidationValidate {

  /**
   * Validates given element.
   *
   * @param array $element
   *   Form element.
   * @param \Drupal\Core\Form\FormStateInterface $formState
   *   Form state.
   * @param array $form
   *   Form constructor.
   */
  public static function validate(array &$element, FormStateInterface $formState, array &$form) {
    $webformKey = $element['#webform_key'];
    $value = $formState->getValue($webformKey);

    // Skip empty unique fields or arrays (aka #multiple).
    if ($value === '' || is_array($value)) {
      return;
    }

    // Test if local authority is to be contacted directly.
    $la = \Drupal::service('fsa_team_finder.get_local_authority')->get($value);
    if (!empty($la)) {
      $fsa_authority = \Drupal::entityTypeManager()
        ->getStorage('fsa_authority')
        ->loadByProperties(['field_mapit_area' => $la['mapit_area']]);
      if ($fsa_authority = reset($fsa_authority)) {
        $error = $fsa_authority->get('field_contact_directly')->getString();
      }
      else {
        $error = FALSE;
      }
    }
    else {
      $error = FALSE;
    }

    // Set error message.
    if ($error) {
      $args = [
        '%name' => $la['name'],
        '@uri' => $fsa_authority->get('field_advice_url')->getString(),
      ];
      $formState->setError(
        $element,
        t('<span>Sorry, %name requires you to make a report directly to them. Please see their <a href="@uri" target="_blank">advice page</a>.</span>', $args)
      );
    }
  }

}
