<?php

namespace Drupal\symfony_mailer_bc;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Mail\MailManager;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\symfony_mailer\EmailFactory;

/**
 * Provides a Symfony Mailer replacement for MailManager.
 */
class MailManagerReplacement extends MailManager {

  /**
   * The email factory.
   *
   * @var \Drupal\symfony_mailer\EmailFactory
   */
  protected $emailFactory;

  /**
   * Constructs the MailManagerReplacement object.
   *
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler to invoke the alter hook with.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The configuration factory.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   The logger channel factory.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $string_translation
   *   The string translation service.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer.
   * @param \Drupal\symfony_mailer\EmailFactory $email_factory;
   *   The email factory.
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler, ConfigFactoryInterface $config_factory, LoggerChannelFactoryInterface $logger_factory, TranslationInterface $string_translation, RendererInterface $renderer, EmailFactory $email_factory) {
    parent::__construct($namespaces, $cache_backend, $module_handler, $config_factory, $logger_factory, $string_translation, $renderer);
    $this->emailFactory = $email_factory;
  }

  /**
   * {@inheritdoc}
   */
  public function mail($module, $key, $to, $langcode, $params = [], $reply = NULL, $send = TRUE) {
    // Call alter hook.
    $context = ['module' => $module, 'to' => $to, 'reply' => $reply, 'entity' => NULL];
    $this->moduleHandler->alter(['mailer_bc', "mailer_bc_$module"], $key, $params, $context);

    if ($entity = $context['entity']) {
      $email = $this->emailFactory->newEntityEmail($entity, $key);
    }
    else {
      $email = $this->emailFactory->newModuleEmail($module, $key);
    }

    $email->setTo($context['to'])
      ->setLangcode($langcode)
      ->setParams($params);
    if ($context['reply']) {
      $email->setReplyTo($reply);
    }

    $result = $email->send();
    // Set the 'send' element for Webform module.
    return ['result' => $result, 'send' => TRUE];
  }

}
