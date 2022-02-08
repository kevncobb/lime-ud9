<?php

namespace Drupal\feedback\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\feedback\FeedbackMessageInterface;
use Drupal\node\NodeInterface;
use Drupal\user\UserInterface;

/**
 * Defines the Feedback message entity.
 *
 * @ingroup feedback
 *
 * @ContentEntityType(
 *   id = "feedback_message",
 *   label = @Translation("Feedback message"),
 *   bundle_label = @Translation("Feedback message type"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\feedback\FeedbackMessageListBuilder",
 *     "views_data" = "Drupal\feedback\Entity\FeedbackMessageViewsData",
 *
 *     "form" = {
 *       "default" = "Drupal\feedback\Form\FeedbackMessageForm",
 *       "add" = "Drupal\feedback\Form\FeedbackMessageForm",
 *       "edit" = "Drupal\feedback\Form\FeedbackMessageForm",
 *       "delete" = "Drupal\feedback\Form\FeedbackMessageDeleteForm",
 *     },
 *     "access" = "Drupal\feedback\FeedbackMessageAccessControlHandler",
 *     "route_provider" = {
 *       "html" = "Drupal\feedback\FeedbackMessageHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "feedback_message",
 *   admin_permission = "administer feedback message entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "bundle" = "type",
 *     "uuid" = "uuid",
 *     "uid" = "user_id",
 *     "langcode" = "langcode",
 *     "status" = "status",
 *   },
 *   links = {
 *     "canonical" = "/admin/content/feedback_message/{feedback_message}",
 *     "add-form" = "/admin/content/feedback_message/add/{feedback_message_type}",
 *     "edit-form" = "/admin/content/feedback_message/{feedback_message}/edit",
 *     "delete-form" = "/admin/content/feedback_message/{feedback_message}/delete",
 *     "collection" = "/admin/content/feedback_message",
 *   },
 *   bundle_entity_type = "feedback_message_type",
 *   field_ui_base_route = "entity.feedback_message_type.edit_form"
 * )
 */
class FeedbackMessage extends ContentEntityBase implements FeedbackMessageInterface {
  use EntityChangedTrait;

  /**
   * {@inheritdoc}
   */
  public static function preCreate(EntityStorageInterface $storage_controller, array &$values) {
    parent::preCreate($storage_controller, $values);
    $values += [
      'user_id' => \Drupal::currentUser()->id(),
      // 'link' => self::getCurrentPath(),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function label() {
    $label = $this->getEntityType()->getLabel() . " " . $this->id();
    return $label;
  }

  /**
   * {@inheritdoc}
   */
  public function getType() {
    return $this->bundle();
  }

  /**
   * {@inheritdoc}
   */
  public function getPath() {
    return $this->get('link')->value;
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
    $this->set('status', $published ? NodeInterface::PUBLISHED : NodeInterface::NOT_PUBLISHED);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields['id'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('ID'))
      ->setDescription(t('The ID of the Feedback message entity.'))
      ->setReadOnly(TRUE);
    $fields['type'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Type'))
      ->setDescription(t('The Feedback message type/bundle.'))
      ->setSetting('target_type', 'feedback_message_type')
      ->setRequired(TRUE);
    $fields['uuid'] = BaseFieldDefinition::create('uuid')
      ->setLabel(t('UUID'))
      ->setDescription(t('The UUID of the Feedback message entity.'))
      ->setReadOnly(TRUE);

    $fields['user_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Authored by'))
      ->setDescription(t('The user ID of author of the Feedback message entity.'))
      ->setRevisionable(TRUE)
      ->setSetting('target_type', 'user')
      ->setSetting('handler', 'default')
      ->setDefaultValueCallback('Drupal\node\Entity\Node::getCurrentUserId')
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

    $fields['link'] = BaseFieldDefinition::create('uri')
      ->setLabel(t('Path'))
      ->setDescription(t('The location this message was recorded on.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'weight' => 4,
      ])
      ->setDisplayConfigurable('view', TRUE);

    $fields['status'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Publishing status'))
      ->setDescription(t('A boolean indicating whether the Feedback message is published.'))
      ->setDefaultValue(TRUE);

    $fields['langcode'] = BaseFieldDefinition::create('language')
      ->setLabel(t('Language code'))
      ->setDescription(t('The language code for the Feedback message entity.'))
      ->setDisplayOptions('form', [
        'type' => 'language_select',
        'weight' => 10,
      ])
      ->setDisplayConfigurable('form', TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last edited.'));

    return $fields;
  }

}
