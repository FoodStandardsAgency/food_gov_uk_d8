<?php

namespace Drupal\fsa_managed_links\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for FSA managed link edit forms.
 *
 * @ingroup fsa_managed_links
 */
class FsaManagedLinkForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    /* @var $entity \Drupal\fsa_managed_links\Entity\FsaManagedLink */
    $form = parent::buildForm($form, $form_state);

    $entity = $this->entity;

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
        drupal_set_message($this->t('Created the %label FSA managed link.', [
          '%label' => $entity->label(),
        ]));
        break;

      default:
        drupal_set_message($this->t('Saved the %label FSA managed link.', [
          '%label' => $entity->label(),
        ]));
    }
    $form_state->setRedirect('entity.fsa_managed_link.canonical', ['fsa_managed_link' => $entity->id()]);
  }

}
