<?php

namespace Drupal\symfony_mailer\Processor;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Config\ConfigFactoryOverrideInterface;
use Drupal\Core\Config\StorageInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;

/**
 * Symfony Mailer configuration override.
 */
class MailerConfigOverride implements ConfigFactoryOverrideInterface {

  /**
   * Whether cache has been built.
   *
   * @var bool
   */
  protected $builtCache = FALSE;

  /**
   * Array of config overrides.
   *
   * As required by ConfigFactoryOverrideInterface::loadOverrides().
   *
   * @var array
   */
  protected $configOverrides = [];

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The email builder manager.
   *
   * @var \Drupal\symfony_mailer\Processor\EmailBuilderManagerInterface
   */
  protected $builderManager;

  /**
   * Constructs the MailerConfigOverride object.
   *
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   * @param \Drupal\symfony_mailer\Processor\EmailBuilderManagerInterface $email_builder_manager
   *   The email builder manager.
   */
  public function __construct(ModuleHandlerInterface $module_handler, EmailBuilderManagerInterface $email_builder_manager) {
    $this->moduleHandler = $module_handler;
    $this->builderManager = $email_builder_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function loadOverrides($names) {
    $this->buildCache();
    return array_intersect_key($this->configOverrides, array_flip($names));
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheSuffix() {
    return 'MailerConfigOverride';
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheableMetadata($name) {
    return new CacheableMetadata();
  }

  /**
   * {@inheritdoc}
   */
  public function createConfigObject($name, $collection = StorageInterface::DEFAULT_COLLECTION) {
    return NULL;
  }

  /**
   * Build cache of config overrides.
   */
  protected function buildCache() {
    if (!$this->builtCache && $this->moduleHandler->isLoaded()) {
      // Getting the definitions can cause reading of config which triggers
      // `loadOverrides()` to call this function. Protect against an infinite
      // loop by marking the cache as built before starting.
      $this->builtCache = TRUE;

      foreach ($this->builderManager->getDefinitions() as $definition) {
        $this->configOverrides = array_merge($this->configOverrides, $definition['config_overrides']);
      }
    }
  }

}
