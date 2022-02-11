<?php

namespace Drupal\symfony_mailer;

use Drupal\Core\Entity\EntityInterface;
use Drupal\symfony_mailer\Processor\EmailProcessorInterface;

/**
 * Defines the interface for an Email.
 */
interface EmailInterface extends BaseEmailInterface {

  /**
   * Add an email processor.
   *
   * Valid: before building.
   *
   * @param \Drupal\symfony_mailer\Processor\EmailProcessorInterface $processor
   *   The email processor.
   *
   * @return $this
   */
  public function addProcessor(EmailProcessorInterface $processor);

  /**
   * Sets the langcode.
   *
   * Valid: before building.
   *
   * @param string $langcode
   *   Language code to use to compose the email.
   *
   * @return $this
   */
  public function setLangcode(string $langcode);

  /**
   * Gets the langcode.
   *
   * @return string
   *   Language code to use to compose the email.
   */
  public function getLangcode();

  /**
   * Sets parameters used for building the email.
   *
   * Valid: before building.
   *
   * @param array $params
   *   (optional) An array of keyed objects or configuration.
   *
   * @return $this
   */
  public function setParams(array $params = []);

  /**
   * Adds a parameter used for building the email.
   *
   * If the value is an entity, then the key should be the entity type ID.
   * Otherwise the value is typically a setting that alters the email build.
   *
   * Valid: before building.
   *
   * @param string $key
   *   Parameter key to set.
   * @param $value
   *   Parameter value to set.
   *
   * @return $this
   */
  public function setParam(string $key, $value);

  /**
   * Gets parameters used for building the email.
   *
   * @return array
   *   An array of keyed objects.
   */
  public function getParams();

  /**
   * Gets a parameter used for building the email.
   *
   * @param string $key
   *   Parameter key to get.
   *
   * @return mixed
   *   Parameter value, or NULL if the parameter is not set.
   */
  public function getParam(string $key);

  /**
   * Sends the email.
   *
   * Valid: before building.
   *
   * @return bool
   *   Whether successful.
   */
  public function send();

  /**
   * Sets the unrendered email body.
   *
   * The email body will be rendered using a template, then used to form both
   * the HTML and plain text body parts. This process can be customised by the
   * email adjusters added to the email.
   *
   * Valid: before rendering.
   *
   * @param $body
   *   Unrendered email body.
   *
   * @return $this
   */
  public function setBody($body);

  /**
   * Appends content to the email body.
   *
   * Valid: before rendering.
   *
   * @param $body
   *   Unrendered body part to append to the existing body array.
   *
   * @return $this
   */
  public function appendBody($body);

  /**
   * Appends a rendered entity to the email body.
   *
   * Valid: before rendering.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity to render.
   * @param string $view_mode
   *   (optional) The view mode that should be used to render the entity.
   *
   * @return $this
   */
  public function appendBodyEntity(EntityInterface $entity, $view_mode = 'full');

  /**
   * Gets the unrendered email body.
   *
   * Valid: before rendering.
   *
   * @return array
   *   Body render array.
   */
  public function getBody();

  /**
   * Sets variables available in the email template.
   *
   * Valid: before rendering.
   *
   * @param array $variables
   *   An array of keyed variables.
   *
   * @return $this
   */
  public function setVariables(array $variables);

  /**
   * Sets a variable available in the email template.
   *
   * Valid: before rendering.
   *
   * @param string $key
   *   Variable key to set.
   * @param $value
   *   Variable value to set.
   *
   * @return $this
   */
  public function setVariable(string $key, $value);

  /**
   * Gets variables available in the email template.
   *
   * @return array
   *   An array of keyed variables.
   */
  public function getVariables();

  /**
   * Gets the email type.
   *
   * If the email is associated with a config entity, then this is the entity
   * type, else it is the module name.
   *
   * @return string
   *   Email type.
   */
  public function getType();

  /**
   * Gets the email sub-type.
   *
   * The sub-type is a 'key' to distinguish different categories of email with
   * the same type. Emails with the same sub-type are all built in the same
   * way, differently from other sub-types.
   *
   * @return string
   *   Email sub-type.
   */
  public function getSubType();

  /**
   * Gets the associated config entity.
   *
   * The ID of this entity can be used to select a specific template or apply
   * specific policy configuration.
   *
   * @return ?\Drupal\Core\Config\Entity\ConfigEntityInterface.
   *   Entity, or NULL if there is no associated entity.
   */
  public function getEntity();

  /**
   * Gets an array of 'suggestions'.
   *
   * The suggestions are used to select email builders, templates and policy
   * configuration in based on email type, sub-type and associated entity ID.
   *
   * @param string $initial
   *   The initial suggestion.
   * @param string $join
   *   The 'glue' to join each suggestion part with.
   *
   * @return array
   *   Suggestions, formed by taking the initial value and incrementally adding
   *   the type, sub-type and entity ID.
   */
  public function getSuggestions(string $initial, string $join);

  /**
   * Sets the email theme.
   *
   * Valid: before building.
   *
   * @param string $theme_name
   *   The theme name to use for email.
   *
   * @return $this
   */
  public function setTheme(string $theme_name);

  /**
   * Gets the email theme name.
   *
   * @return string
   *   The email theme name.
   */
  public function getTheme();

  /**
   * Adds an asset library to use as mail CSS.
   *
   * @param string $library
   *   Library name, in the form "THEME/LIBRARY".
   *
   * @return $this
   */
  public function addLibrary(string $library);

  /**
   * Gets the libraries to use as mail CSS.
   *
   * @return array
   *   Array of library names, in the form "THEME/LIBRARY".
   */
  public function getLibraries();

  /**
   * Sets the mailer transport DSN to use.
   *
   * @param string $dsn
   *   Symfony mailer transport DSN.
   *
   * @return $this
   */
  public function setTransportDsn(string $dsn);

  /**
   * Gets the mailer transport DSN that will be used.
   *
   * @return string
   *   Transport DSN.
   */
  public function getTransportDSN();

}
