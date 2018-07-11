<?php

namespace Drupal\managed_links\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for Managed Link edit forms.
 *
 * @ingroup managed_links
 */
class ManagedLinkForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    /* @var $entity \Drupal\managed_links\Entity\ManagedLink */
    $form = parent::buildForm($form, $form_state);
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $entity = $this->entity;

    $status = parent::save($form, $form_state);

    switch ($status) {
      case SAVED_NEW:
        drupal_set_message($this->t('Created the %label Managed Link.', [
          '%label' => $entity->label(),
        ]));
        break;

      default:
        drupal_set_message($this->t('Saved the %label Managed Link.', [
          '%label' => $entity->label(),
        ]));
    }
    $form_state->setRedirect('entity.managed_link.canonical', ['managed_link' => $entity->id()]);
  }

}
