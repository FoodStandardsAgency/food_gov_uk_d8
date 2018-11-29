<?php

namespace Drupal\fsa_ratings\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for FSA Authority edit forms.
 *
 * @ingroup fsa_ratings
 */
class FsaAuthorityForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    /* @var $entity \Drupal\fsa_ratings\Entity\FsaAuthority */
    $form = parent::buildForm($form, $form_state);

    $entity = $this->entity;

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $entity = &$this->entity;

    $status = parent::save($form, $form_state);

    switch ($status) {
      case SAVED_NEW:
        drupal_set_message($this->t('Created the %label FSA Authority.', [
          '%label' => $entity->label(),
        ]));
        break;

      default:
        drupal_set_message($this->t('Saved the %label FSA Authority.', [
          '%label' => $entity->label(),
        ]));
    }
    $form_state->setRedirect('entity.fsa_authority.canonical', ['fsa_authority' => $entity->id()]);
  }

}
