<?php

namespace Drupal\moderation_note;

/**
 * Provides an interface defining a Moderation Note Menu Count.
 */
interface ModerationNoteMenuCountInterface {

  /**
   * Updates the Assigned Notes count.
   */
  public function assignedTo(int $uid);

  /**
   * Updates the View Notes tab count.
   */
  public function contentLink(string $entity_type, int $entity_id);

}
