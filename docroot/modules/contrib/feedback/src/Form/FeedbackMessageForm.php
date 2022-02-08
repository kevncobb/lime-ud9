<?php

namespace Drupal\feedback\Form;

use Drupal\Core\Config\Entity\ConfigEntityStorage;
use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form controller for Feedback message edit forms.
 *
 * @ingroup feedback
 */
class FeedbackMessageForm extends ContentEntityForm {

  /**
   * The feedback_message_type storage.
   *
   * @var \Drupal\Core\Config\Entity\ConfigEntityStorage
   */
  protected $feedbackMessageTypeStorage;

  /**
   * Constructs a ContentEntityForm object.
   *
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entityRepository
   *   The entity manager.
   * @param \Drupal\Core\Config\Entity\ConfigEntityStorage $feedback_message_type_storage
   *   The feedback message type storage.
   */
  public function __construct(EntityRepositoryInterface $entityRepository, ConfigEntityStorage $feedback_message_type_storage) {
    parent::__construct($entityRepository);
    $this->feedbackMessageTypeStorage = $feedback_message_type_storage;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.repository'),
      $container->get('entity_type.manager')->getStorage('feedback_message_type')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    /** @var \Drupal\feedback\Entity\FeedbackMessage $entity */
    $entity = $this->entity;
    $status = parent::save($form, $form_state);

    switch ($status) {
      case SAVED_NEW:
        /** @var FeedbackMessageType $message_type */
        $message_type = $this->feedbackMessageTypeStorage->load($entity->getType());
        $this->messenger()->addStatus($message_type->getSuccessMessage());
        break;

      default:
        $this->messenger()->addStatus($this->t('Saved the %label Feedback message.', [
          '%label' => $entity->label(),
        ]));
    }
  }

}
