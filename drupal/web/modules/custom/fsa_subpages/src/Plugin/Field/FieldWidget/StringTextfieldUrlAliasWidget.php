<?php

namespace Drupal\fsa_subpages\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\Plugin\Field\FieldWidget\StringTextfieldWidget;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'string_textfield_url_alias' widget.
 *
 * @FieldWidget(
 *   id = "string_textfield_url_alias",
 *   label = @Translation("Textfield URL Alias"),
 *   field_types = {
 *     "string"
 *   }
 * )
 */
class StringTextfieldUrlAliasWidget extends StringTextfieldWidget {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $render = parent::formElement($items, $delta, $element, $form, $form_state);
    $render['#element_validate'][] = [static::class, 'validateUrlAlias'];
    return $render;
  }

  /**
   * Validate url alias.
   */
  public static function validateUrlAlias($element, FormStateInterface $form_state) {
    $alias = $element['value']['#value'];
    $rx = '/^[a-z][a-z0-9\/-]+[a-z0-9]$/';
    if (!preg_match($rx, $alias)) {
      $form_state->setError($element, t('Invalid alias "%alias". Must conform to regular expression "%rx".', ['%alias' => $alias, '%rx' => $rx]));
    }
  }

}
