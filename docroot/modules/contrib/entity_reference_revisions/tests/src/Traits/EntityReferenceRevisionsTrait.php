<?php

declare(strict_types = 1);

namespace Drupal\Tests\entity_reference_revisions\Traits;

use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;

/**
 * Helper methods for the entity_reference_revisions module tests.
 */
trait EntityReferenceRevisionsTrait {

  /**
   * Generates and returns an entity_reference_revisions field storage.
   *
   * @param string $entity_type
   *   (optional) The target entity type of the field storage. Defaults to
   *   'node'.
   * @param string $target_entity_type_id
   *   (optional) The referenced target entity type id. Defaults to 'node'.
   * @param string $target_bundle
   *   (optional) The referenced target bundle. Defaults to 'article'.
   *
   * @return \Drupal\field\Entity\FieldConfig
   *   The field storage config entity.
   */
  protected function generateEntityReferenceRevisionField(string $entity_type = 'node', string $target_entity_type_id = 'node', string $target_bundle = 'article') {
    $field_storage = FieldStorageConfig::create([
      'field_name' => 'entity_reference_revisions',
      'entity_type' => $entity_type,
      'type' => 'entity_reference_revisions',
      'settings' => [
        'target_type' => $target_entity_type_id,
      ],
    ]);
    $field_storage->save();

    $field = FieldConfig::create([
      'field_storage' => $field_storage,
      'bundle' => $target_bundle,
    ]);
    $field->save();
    return $field;
  }

}
