<?php

declare(strict_types=1);

namespace Drupal\sdc_display;

use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\TypedData\Exception\MissingDataException;

/**
 * Utility methods.
 */
class Util {

  /**
   * Small helper function to determine if a field item is empty.
   *
   * @param mixed $field_item_list
   *   The field item list from the preprocessed variables.
   * @param int $delta
   *   The field delta.
   *
   * @return bool
   *   TRUE if the field is empty.
   */
  public static function fieldIsEmpty($field_item_list, int $delta): bool {
    if (!$field_item_list instanceof FieldItemListInterface) {
      return FALSE;
    }
    $field_item = NULL;
    try {
      $field_item = $field_item_list->get($delta);
    }
    catch (MissingDataException $e) {
      // Intentionally left blank.
    }
    if (!$field_item instanceof FieldItemInterface) {
      return FALSE;
    }
    return $field_item->isEmpty();
  }

}
