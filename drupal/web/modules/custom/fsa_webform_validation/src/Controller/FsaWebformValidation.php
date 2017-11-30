<?php

namespace Drupal\fsa_webform_validation\Controller;

use Drupal\Core\Link;

/**
 * Report a food safety concern redirect.
 */
class FsaWebformValidation {

  /**
   * Title callback.
   *
   * @return string
   *   Render page title.
   */
  public function title() {
    $nid = \Drupal::request()->get('nid');
    $node = $this->getNode($nid);
    return $node->getTitle();
  }

  /**
   * Renders page.
   *
   * @return array
   *   Render array.
   */
  public function render() {

    // Get FSA authority values.
    $id = \Drupal::request()->get('id');
    $fsa_authority = $this->getFsaAuthority($id);
    if ($fsa_authority) {
      $name = $fsa_authority->name->getString();
      $advice_url = $fsa_authority->field_advice_url->getString();
      $email = $fsa_authority->field_email->getString();
    }

    // Construct back path.
    $nid = \Drupal::request()->get('nid');
    if ($nid) {
      $back = \Drupal::service('path.alias_manager')
        ->getAliasByPath('/node/' . $nid);
    }

    // Construct render array.
    return [
      '#theme' => 'fsa_webform_validation_redirect',
      '#name' => $name,
      '#advice_url' => $advice_url,
      '#email' => $email,
      '#back' => $back,
    ];
  }

  /**
   * Gets FSA authority.
   *
   * @return object|null
   *   FSA authority.
   */
  public function getFsaAuthority($id) {
    return \Drupal::entityTypeManager()->getStorage('fsa_authority')->load($id);
  }

  /**
   * Gets node.
   *
   * @return object|null
   *   Node.
   */
  public function getNode($nid) {
    return \Drupal::entityTypeManager()->getStorage('node')->load($nid);
  }

}