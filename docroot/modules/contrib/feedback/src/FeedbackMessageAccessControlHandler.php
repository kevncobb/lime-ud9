<?php

namespace Drupal\feedback;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Feedback message entity.
 *
 * @see \Drupal\feedback\Entity\FeedbackMessage.
 */
class FeedbackMessageAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\feedback\FeedbackMessageInterface $entity */
    switch ($operation) {
      case 'view':
        if (!$entity->isPublished()) {
          return AccessResult::allowedIfHasPermission($account, 'view unpublished feedback message entities');
        }
        return AccessResult::allowedIfHasPermission($account, 'view published feedback message entities');

      case 'update':
        return AccessResult::allowedIfHasPermission($account, 'edit feedback message entities');

      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'delete feedback message entities');
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add feedback message entities');
  }

  /**
   * {@inheritdoc}
   */
  protected function checkFieldAccess($operation, FieldDefinitionInterface $field_definition, AccountInterface $account, FieldItemListInterface $items = NULL) {
    // Only users with the administer permission can edit administrative fields.
    $administrative_fields = ['path', 'user_id', 'status', 'created'];
    if ($operation == 'edit' && in_array($field_definition->getName(), $administrative_fields, TRUE)) {
      return AccessResult::allowedIfHasPermission($account, 'administer feedback message entities');
    }

    return parent::checkFieldAccess($operation, $field_definition, $account, $items);
  }

}
