<?php

namespace Drupal\symfony_mailer;

use Drupal\Core\Config\Entity\ConfigEntityInterface;

/**
 * Defines a Mailer Transport configuration entity class.
 *
 * @ConfigEntityType(
 *   id = "mailer_transport",
 *   label = @Translation("Mailer Transport"),
 *   handlers = {
 *     "list_builder" = "Drupal\symfony_mailer\MailerTransportListBuilder",
 *     "form" = {
 *       "edit" = "Drupal\symfony_mailer\Form\TransportForm",
 *       "add" = "Drupal\symfony_mailer\Form\TransportAddForm",
 *       "delete" = "Drupal\Core\Entity\EntityDeleteForm"
 *     }
 *   },
 *   admin_permission = "administer mailer",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label"
 *   },
 *   links = {
 *     "edit-form" = "/admin/config/system/mailer/transport/{mailer_transport}",
 *     "delete-form" = "/admin/config/system/mailer/transport/{mailer_transport}/delete",
 *     "set-default" = "/admin/config/system/mailer/transport/{mailer_transport}/set-default",
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "plugin",
 *     "configuration",
 *   }
 * )
 */
interface MailerTransportInterface extends ConfigEntityInterface {

  /**
   * Returns the transport plugin.
   *
   * @return \Drupal\symfony_mailer\TransportPluginInterface
   *   The transport plugin used by this mailer transport entity.
   */
  public function getPlugin();

  /**
   * Returns the transport plugin ID.
   *
   * @return string
   *   The transport plugin ID.
   */
  public function getPluginId();

  /**
   * Sets the transport plugin.
   *
   * @param string $plugin_id
   *   The transport plugin ID.
   */
  public function setPluginId($plugin_id);

  /**
   * Gets the DSN.
   *
   * @return string
   *   The DSN.
   */
  public function getDsn();

  /**
   * Sets this as the default transport.
   */
  public function setAsDefault();

  /**
   * Determines if this is the default transport.
   *
   * @return bool
   *   TRUE if this is the default transport, FALSE otherwise.
   */
  public function isDefault();

}
