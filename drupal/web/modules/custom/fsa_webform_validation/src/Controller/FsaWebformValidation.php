<?php

namespace Drupal\fsa_webform_validation\Controller;

use Drupal\Core\Link;
use Drupal\Core\Url;

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
    if ($node = $this->getNode($nid)) {
      return $node->getTitle();
    }
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
    if (is_numeric($id) && $fsa_authority = $this->getFsaAuthority($id)) {
      $name = $fsa_authority->name->getString();
      $advice_url = $fsa_authority->field_advice_url->getString();
    }
    else {
      $name = '';
      $advice_url = '';
    }

    // Construct back path.
    $nid = \Drupal::request()->get('nid');
    if (is_numeric($nid)) {
      $back_url = Url::fromRoute('entity.node.canonical', ['node' => $nid]);
    }
    else {
      $back_url = '';
    }

    // Construct render array.
    return [
      ['#markup' => t('<h2>Food safety team details</h2>')],
      [
        '#markup' => t('<p>Please report your issue directly to <a href="@advice_url" target="_blank">@name</a> food safety team</p>',
        [
          '@name' => $name,
          '@advice_url' => $advice_url,
        ]),
      ],
      ['#type' => 'link', '#title' => t('Back'), '#url' => $back_url],
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
