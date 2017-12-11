<?php

namespace Drupal\fsa_webform_validation\Validate;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\RedirectResponse;

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
   *
   * @see https://www.drupal.org/docs/8/modules/webform/webform-cookbook/how-to-add-custom-validation-to-a-webform-element
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
        if ($fsa_authority->hasField('field_contact_directly')) {
          $error = $fsa_authority->get('field_contact_directly')->getString();
        }
      }
    }

    // Redirect user.
    if (isset($error) && isset($fsa_authority)) {
      if ($error) {
        $route_parameters = [
          'id' => $fsa_authority->id(),
          'nid' => \Drupal::routeMatch()->getRawParameter('node'),
        ];
        $url = Url::fromRoute('fsa_webform_validation.render', $route_parameters);
        $path = $url->toString();
        $response = new RedirectResponse($path);
        $response->send();
      }
    }
  }

}
