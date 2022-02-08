<?php

namespace Drupal\feedback\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;
use Drupal\feedback\FeedbackMessageTypeInterface;

/**
 * Defines the Feedback message type entity.
 *
 * @ConfigEntityType(
 *   id = "feedback_message_type",
 *   label = @Translation("Feedback message type"),
 *   handlers = {
 *     "list_builder" = "Drupal\feedback\FeedbackMessageTypeListBuilder",
 *     "form" = {
 *       "add" = "Drupal\feedback\Form\FeedbackMessageTypeForm",
 *       "edit" = "Drupal\feedback\Form\FeedbackMessageTypeForm",
 *       "delete" = "Drupal\feedback\Form\FeedbackMessageTypeDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\feedback\FeedbackMessageTypeHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "feedback_message_type",
 *   admin_permission = "administer site configuration",
 *   bundle_of = "feedback_message",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/feedback_message_type/{feedback_message_type}",
 *     "add-form" = "/admin/structure/feedback_message_type/add",
 *     "edit-form" = "/admin/structure/feedback_message_type/{feedback_message_type}/edit",
 *     "delete-form" = "/admin/structure/feedback_message_type/{feedback_message_type}/delete",
 *     "collection" = "/admin/structure/feedback_message_type"
 *   }
 * )
 */
class FeedbackMessageType extends ConfigEntityBundleBase implements FeedbackMessageTypeInterface {
  /**
   * The Feedback message type ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The Feedback message type label.
   *
   * @var string
   */
  protected $label;

  /**
   * Gets the success message for a feedback message type.
   *
   * @return string
   *   The success message.
   */
  public function getSuccessMessage() {
    return $this->get('success_message');
  }

}
