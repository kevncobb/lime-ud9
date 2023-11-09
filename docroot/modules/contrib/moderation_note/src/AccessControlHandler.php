<?php

namespace Drupal\moderation_note;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Defines the access control handler for the moderation_note entity type.
 *
 * @see \Drupal\moderation_note\ModerationNoteInterface
 */
class AccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\moderation_note\ModerationNoteInterface $entity */

    switch ($operation) {
      case 'view':
        return AccessResult::allowedIfHasPermission($account, 'access moderation notes')
          ->orIf(AccessResult::allowedIfHasPermission($account, 'administer moderation notes'))
          ->andIf($entity->getModeratedEntity()->access('view', $account, TRUE))
          ->cachePerPermissions()->cachePerUser()->addCacheableDependency($entity);

      case 'create':
        return AccessResult::allowedIf(_moderation_note_on_entity($entity->getModeratedEntity(), $account))
          ->orIf(AccessResult::allowedIfHasPermission($account, 'administer moderation notes'))
          ->cachePerPermissions()->cachePerUser()->addCacheableDependency($entity);

      case 'update':
        return AccessResult::allowedIf($this->isNoteOwner($entity, $account))
          ->orIf(AccessResult::allowedIfHasPermission($account, 'administer moderation notes'))
          ->andIf(AccessResult::allowedIf($entity->isPublished()))
          ->cachePerPermissions()->cachePerUser()->addCacheableDependency($entity);

      case 'delete':
        return AccessResult::allowedIf($this->isNoteOwner($entity, $account))
          ->orIf(AccessResult::allowedIfHasPermission($account, 'administer moderation notes'))
          ->andIf(AccessResult::allowedIf($entity->hasParent() || !$entity->isPublished()))
          ->cachePerPermissions()->cachePerUser()->addCacheableDependency($entity);

      case 'reply':
        return AccessResult::allowedIfHasPermission($account, 'create moderation note replies')
          ->orIf(AccessResult::allowedIfHasPermission($account, 'create moderation notes'))
          ->orIf(AccessResult::allowedIfHasPermission($account, 'administer moderation notes'))
          ->cachePerPermissions()->cachePerUser()->addCacheableDependency($entity);

      case 'resolve':
        return AccessResult::allowedIf($this->isNoteOwner($entity, $account))
          ->orIf(AccessResult::allowedIfHasPermission($account, 'administer moderation notes'))
          ->orIf(AccessResult::allowedIf(
            AccessResult::allowedIfHasPermission($account, 'resolve moderation notes on editable entities')->isAllowed()
            && $entity->getModeratedEntity()->access('update', $account, TRUE)
          ))

          ->andIf(AccessResult::allowedIf(!$entity->hasParent()))
          ->cachePerPermissions()->cachePerUser()->addCacheableDependency($entity);

      default:
        // No opinion.
        return AccessResult::neutral()->cachePerPermissions();
    }
  }

  /**
   * Check if the current account is the note owner.
   */
  protected function isNoteOwner(EntityInterface $entity, AccountInterface $account) {
    /** @var \Drupal\moderation_note\ModerationNoteInterface $entity */
    if (!$account->id() || !$entity->getOwner()) {
      return FALSE;
    }
    return ($account->id() === $entity->getOwner()->id());
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'create moderation notes');
  }

}
