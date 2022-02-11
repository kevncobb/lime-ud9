<?php

namespace Drupal\symfony_mailer\Plugin\EmailAdjuster;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\symfony_mailer\Processor\EmailAdjusterBase;
use Drupal\symfony_mailer\Entity\MailerTransport;
use Drupal\symfony_mailer\EmailInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Mime\Address;

/**
 * Defines the Default headers Email Adjuster.
 *
 * @EmailAdjuster(
 *   id = "mailer_default_headers",
 *   label = @Translation("Default headers"),
 *   description = @Translation("Set default headers."),
 *   weight = 100,
 * )
 */
class DefaultsEmailAdjuster extends EmailAdjusterBase implements ContainerFactoryPluginInterface {

  /**
   * The configuration factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Constructs a new DefaultsEmailAdjuster object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The configuration factory.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ConfigFactoryInterface $config_factory) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('config.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function preRender(EmailInterface $email) {
    $site_config = $this->configFactory->get('system.site');
    $site_mail = $site_config->get('mail') ?: ini_get('sendmail_from');
    $sender = new Address($site_mail, $site_config->get('name'));
    $theme = $email->getTheme();
    $email->setSender($sender)
      ->addTextHeader('X-Mailer', 'Drupal')
      ->addLibrary("$theme/email");

    if ($default_transport = MailerTransport::loadDefault()) {
      $email->setTransportDsn($default_transport->getDsn());
    }
  }

}
