<?php

namespace Drupal\symfony_mailer\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\Core\Entity\EntityWithPluginCollectionInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\symfony_mailer\Processor\AdjusterPluginCollection;

/**
 * Defines a Mailer Policy configuration entity class.
 *
 * @ConfigEntityType(
 *   id = "mailer_policy",
 *   label = @Translation("Mailer Policy"),
 *   handlers = {
 *     "list_builder" = "Drupal\symfony_mailer\MailerPolicyListBuilder",
 *     "form" = {
 *       "edit" = "Drupal\symfony_mailer\Form\PolicyEditForm",
 *       "add" = "Drupal\symfony_mailer\Form\PolicyAddForm",
 *       "delete" = "Drupal\Core\Entity\EntityDeleteForm"
 *     }
 *   },
 *   admin_permission = "administer mailer",
 *   entity_keys = {
 *     "id" = "id",
 *   },
 *   links = {
 *     "edit-form" = "/admin/config/system/mailer/policy/{mailer_policy}",
 *     "delete-form" = "/admin/config/system/mailer/policy/{mailer_policy}/delete",
 *     "collection" = "/admin/config/system/mailer/policy",
 *   },
 *   config_export = {
 *     "id",
 *     "configuration",
 *   }
 * )
 */
class MailerPolicy extends ConfigEntityBase implements EntityWithPluginCollectionInterface {
  use StringTranslationTrait;

  /**
   * The unique ID of the policy record.
   *
   * @var string
   */
  protected $id;

  /**
   * The email builder manager.
   *
   * @var \Drupal\symfony_mailer\Processor\EmailBuilderManager
   */
  protected $emailBuilderManager;

  /**
   * The email adjuster manager.
   *
   * @var \Drupal\symfony_mailer\Processor\EmailAdjusterManager
   */
  protected $emailAdjusterManager;

  protected $type;
  protected $subType;
  protected $entity;
  protected $entityLabel;
  protected $builderDefinition;

  /**
   * Email builder configuration for this policy record.
   *
   * An associative array of email adjuster configuration, keyed by the plug-in
   * ID with value as an array of configured settings.
   */
  protected $configuration = [];

  /**
   * The collection of email adjuster plug-ins configured in this policy.
   *
   * @var \Drupal\Core\Plugin\DefaultLazyPluginCollection;
   */
  protected $pluginCollection;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $values, $entity_type) {
    parent::__construct($values, $entity_type);
    $this->emailBuilderManager = \Drupal::service('plugin.manager.email_builder');
    $this->emailAdjusterManager = \Drupal::service('plugin.manager.email_adjuster');
    $this->labelUnknown = $this->t('Unknown');
    $this->labelAll = $this->t('<b>*All*</b>');
    $this->labelInvalid = $this->t('<b>*Invalid*</b>');

    // The root policy with ID '_' applies to all types.
    if (!$this->id || ($this->id == '_')) {
      $this->builderDefinition = ['label' => $this->labelAll];
      return;
    }

    list($this->type, $this->subType, $entityId) = array_pad(explode('.', $this->id), 3, NULL);
    $this->builderDefinition = $this->emailBuilderManager->getDefinition($this->type, FALSE);
    if (!$this->builderDefinition) {
      $this->builderDefinition = ['label' => $this->labelUnknown];
    }
    if ($entityId && !empty($this->builderDefinition['has_entity'])) {
      $this->entity = $this->entityTypeManager()->getStorage($this->type)->load($entityId);
    }
  }

  /**
   * Gets the email type this policy applies to.
   *
   * @return ?string
   *   Email type, or NULL if the policy applies to all types.
   */
  public function getType() {
    return $this->type;
  }

  /**
   * Gets the email sub-type this policy applies to.
   *
   * @return ?string
   *   Email sub-type, or NULL if the policy applies to all sub-types.
   */
  public function getSubType() {
    return $this->subType;
  }

  /**
   * Gets the config entity this policy applies to.
   *
   * @return ?\Drupal\Core\Config\Entity\ConfigEntityInterface.
   *   Entity, or NULL if the policy applies to all entities.
   */
  public function getEntity() {
    return $this->entity;
  }

  /**
   * Gets a human-readable label for the email type this policy applies to.
   *
   * @return string
   *   Email type label.
   */
  public function getTypeLabel() {
    return $this->builderDefinition['label'];
  }

  /**
   * Gets a human-readable label for the the email sub-type.
   *
   * @return string
   *   Email sub-type label.
   */
  public function getSubTypeLabel() {
    if ($this->subType) {
      if ($sub_types = $this->builderDefinition['sub_types'] ?? []) {
        return $sub_types[$this->subType] ?? $this->labelUnknown;
      }
      return $this->subType;
    }
    return $this->labelAll;
  }

  /**
   * Gets a human-readable label for the config entity this policy applies to.
   *
   * @return string
   *   Email config entity label, or NULL if the policy applies to all
   *   entities.
   */
  public function getEntityLabel() {
    return $this->entity ? $this->entity->label() : NULL;
  }

  /**
   * Sets the email adjuster configuration for this policy record.
   *
   * @param array $configuration
   *   An associative array of adjuster configuration, keyed by the plug-in ID
   *   with value as an array of configured settings.
   *
   * @return $this
   */
  public function setConfiguration(array $configuration) {
    $this->configuration = $configuration;
    if ($this->pluginCollection) {
      $this->pluginCollection->setConfiguration($configuration);
    }
    return $this;
  }

  /**
   * Gets the email adjuster configuration for this policy record.
   *
   * @return array
   *   An associative array of adjuster configuration, keyed by the plug-in ID
   *   with value as an array of configured settings.
   */
  public function getConfiguration() {
    return $this->configuration;
  }

  /**
   * Returns the ordered collection of configured adjuster plugin instances.
   *
   * @return \Drupal\symfony_mailer\Processor\AdjusterPluginCollection
   *   The adjuster collection.
   */
  public function adjusters() {
    if (!isset($this->pluginCollection)) {
      $this->pluginCollection = new AdjusterPluginCollection($this->emailAdjusterManager, $this->configuration);
    }
    return $this->pluginCollection;
  }

  /**
   * Returns all available adjuster plugin definitions.
   *
   * @return array
   *   An associative array of plugin definitions, keyed by the plug-in ID.
   */
  public function adjusterDefinitions() {
    return $this->emailAdjusterManager->getDefinitions();
  }

  /**
   * Gets a short human-readable summary of the configured policy.
   *
   * @return string
   *   Summary text.
   */
  public function getSummary() {
    $summary = [];
    foreach (array_keys($this->getConfiguration()) as $plugin_id) {
      if ($definition = $this->emailAdjusterManager->getDefinition($plugin_id, FALSE)) {
        $summary[] = $definition['label'];
      }
    }
    return implode(', ', $summary);
  }

  /**
   * {@inheritdoc}
   */
  public function getPluginCollections() {
    return ['adjusters' => $this->adjusters()];
  }

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    parent::calculateDependencies();
    if ($this->entity) {
      $this->addDependency('config', $this->entity->getConfigDependencyName());
    }
    elseif ($provider = $this->builderDefinition['provider'] ?? NULL) {
      $this->addDependency('module', $provider);
    }
    return $this;
  }

  /**
   * Helper callback to sort entities.
   */
  public static function sort(ConfigEntityInterface $a, ConfigEntityInterface $b) {
    return strnatcasecmp($a->getTypeLabel(), $b->getTypeLabel()) ?:
      strnatcasecmp($a->getSubTypeLabel(), $b->getSubTypeLabel()) ?:
      strnatcasecmp($a->getEntityLabel(), $b->getEntityLabel());
  }

}
