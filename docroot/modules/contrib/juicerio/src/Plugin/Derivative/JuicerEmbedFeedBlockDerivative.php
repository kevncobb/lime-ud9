<?php

namespace Drupal\juicerio\Plugin\Derivative;

/**
 * @file
 * Contains \Drupal\juicerio\src\Plugin\Derivative\JuicerEmbedFeedBlockDerivative.
 */

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class JuicerEmbedFeedBlockDerivative.
 *
 * @package Drupal\juicerio\Plugin\Derivative.
 */
class JuicerEmbedFeedBlockDerivative extends DeriverBase implements ContainerDeriverInterface {

  use StringTranslationTrait;


  /**
   * The Drupal config factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Constructs a global locator.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The Drupal config factory service.
   */
  public function __construct(ConfigFactoryInterface $config_factory) {
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $base_plugin_id) {
    return new static(
      $container->get('config.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {
    $count = $this->configFactory->get('juicerio.settings')->get('juicer_blocks');

    for ($delta = 1; $delta <= $count; $delta++) {
      $info = $this->t('Juicer Embed Feed');
      $this->derivatives['juicerio_' . $delta] = $base_plugin_definition;
      $this->derivatives['juicerio_' . $delta]['admin_label'] = $this->t('@info @delta', ['@info' => $info, '@delta' => $delta]);
    }

    return $this->derivatives;
  }

}
