<?php

declare(strict_types = 1);

namespace Drupal\sendgrid_api;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\key\KeyInterface;
use Drupal\key\KeyRepositoryInterface;
use Drupal\sendgrid_api\Exception\SendGridApiInvalidKeyException;

/**
 * Service for managing API key.
 */
class SendGridApiKey implements SendGridApiKeyInterface {

  /**
   * Config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Repository for Key configuration entities.
   *
   * @var \Drupal\key\KeyRepositoryInterface
   */
  protected $keyRepository;

  /**
   * Constructs a new SendGridApiKey.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   Config factory.
   * @param \Drupal\key\KeyRepositoryInterface $keyRepository
   *   Repository for Key configuration entities.
   */
  public function __construct(ConfigFactoryInterface $configFactory, KeyRepositoryInterface $keyRepository) {
    $this->configFactory = $configFactory;
    $this->keyRepository = $keyRepository;
  }

  /**
   * {@inheritdoc}
   */
  public function getApiKey(): string {
    $key = $this->configFactory->get('sendgrid_api.settings')->get('api_key');
    if (empty($key)) {
      throw new SendGridApiInvalidKeyException();
    }

    $key = $this->keyRepository->getKey($key);
    if (!$key instanceof KeyInterface) {
      throw new SendGridApiInvalidKeyException();
    }

    return $key->getKeyValue();
  }

}
