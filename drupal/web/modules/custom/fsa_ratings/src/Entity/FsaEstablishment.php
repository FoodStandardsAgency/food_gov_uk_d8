<?php

namespace Drupal\fsa_ratings\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\user\UserInterface;

/**
 * Defines the FSA Establishment entity.
 *
 * @ingroup fsa_ratings
 *
 * @ContentEntityType(
 *   id = "fsa_establishment",
 *   label = @Translation("FSA Establishment"),
 *   handlers = {
 *     "view_builder" = "Drupal\fsa_ratings\FsaEstablishmentViewBuilder",
 *     "list_builder" = "Drupal\fsa_ratings\FsaEstablishmentListBuilder",
 *     "views_data" = "Drupal\fsa_ratings\Entity\FsaEstablishmentViewsData",
 *     "translation" = "Drupal\fsa_ratings\FsaEstablishmentTranslationHandler",
 *
 *     "form" = {
 *       "default" = "Drupal\fsa_ratings\Form\FsaEstablishmentForm",
 *       "add" = "Drupal\fsa_ratings\Form\FsaEstablishmentForm",
 *       "edit" = "Drupal\fsa_ratings\Form\FsaEstablishmentForm",
 *       "delete" = "Drupal\fsa_ratings\Form\FsaEstablishmentDeleteForm",
 *     },
 *     "access" = "Drupal\fsa_ratings\FsaEstablishmentAccessControlHandler",
 *     "route_provider" = {
 *       "html" = "Drupal\fsa_ratings\FsaEstablishmentHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "fsa_establishment",
 *   data_table = "fsa_establishment_field_data",
 *   translatable = TRUE,
 *   admin_permission = "administer fsa establishment entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "name",
 *     "uuid" = "uuid",
 *     "uid" = "user_id",
 *     "langcode" = "langcode",
 *     "status" = "status",
 *   },
 *   links = {
 *     "canonical" = "/hygiene-ratings/{fsa_establishment}",
 *     "add-form" = "/hygiene-ratings/add",
 *     "edit-form" = "/hygiene-ratings/{fsa_establishment}/edit",
 *     "delete-form" = "/hygiene-ratings/{fsa_establishment}/delete",
 *     "collection" = "/hygiene-ratings",
 *   },
 *   field_ui_base_route = "fsa_establishment.settings"
 * )
 */
class FsaEstablishment extends ContentEntityBase implements FsaEstablishmentInterface {

  use EntityChangedTrait;

  /**
   * {@inheritdoc}
   */
  public static function preCreate(EntityStorageInterface $storage_controller, array &$values) {
    parent::preCreate($storage_controller, $values);
    $values += [
      'user_id' => \Drupal::currentUser()->id(),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return $this->get('name')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setName($name) {
    $this->set('name', $name);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getCreatedTime() {
    return $this->get('created')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setCreatedTime($timestamp) {
    $this->set('created', $timestamp);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwner() {
    return $this->get('user_id')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwnerId() {
    return $this->get('user_id')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwnerId($uid) {
    $this->set('user_id', $uid);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwner(UserInterface $account) {
    $this->set('user_id', $account->id());
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function isPublished() {
    return (bool) $this->getEntityKey('status');
  }

  /**
   * {@inheritdoc}
   */
  public function setPublished($published) {
    $this->set('status', $published ? TRUE : FALSE);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['user_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Authored by'))
      ->setDescription(t('The user ID of author of the FSA Establishment entity.'))
      ->setRevisionable(TRUE)
      ->setSetting('target_type', 'user')
      ->setSetting('handler', 'default')
      ->setTranslatable(TRUE)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'author',
        'weight' => 0,
      ])
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'weight' => 5,
        'settings' => [
          'match_operator' => 'CONTAINS',
          'size' => '60',
          'autocomplete_type' => 'tags',
          'placeholder' => '',
        ],
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Name'))
      ->setDescription(t('The name of the FSA Establishment entity.'))
      ->setSettings([
        'max_length' => 255,
        'text_processing' => 0,
      ])
      ->setDefaultValue('')
      ->setTranslatable(TRUE)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'string',
        'weight' => -4,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -4,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['status'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Publishing status'))
      ->setDescription(t('A boolean indicating whether the FSA Establishment is published.'))
      ->setDefaultValue(TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last edited.'));

    return $fields;
  }

}
