<?php

namespace Drupal\feedback;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Feedback message entities.
 *
 * @ingroup feedback
 */
interface FeedbackMessageInterface extends ContentEntityInterface, EntityChangedInterface, EntityOwnerInterface {

  /**
   * Gets the Feedback message type.
   *
   * @return string
   *   The Feedback message type.
   */
  public function getType();

  /**
   * Gets the Feedback message path.
   *
   * @return string
   *   Path of the Feedback message.
   */
  public function getPath();

  /**
   * Gets the Feedback message creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Feedback message.
   */
  public function getCreatedTime();

  /**
   * Sets the Feedback message creation timestamp.
   *
   * @param int $timestamp
   *   The Feedback message creation timestamp.
   *
   * @return \Drupal\feedback\FeedbackMessageInterface
   *   The called Feedback message entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Returns the Feedback message published status indicator.
   *
   * Unpublished Feedback message are only visible to restricted users.
   *
   * @return bool
   *   TRUE if the Feedback message is published.
   */
  public function isPublished();

  /**
   * Sets the published status of a Feedback message.
   *
   * @param bool $published
   *   TRUE to set this Feedback message to published, FALSE to set it to
   *   unpublished.
   *
   * @return \Drupal\feedback\FeedbackMessageInterface
   *   The called Feedback message entity.
   */
  public function setPublished($published);

}
