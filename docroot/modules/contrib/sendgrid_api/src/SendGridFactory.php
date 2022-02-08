<?php

declare(strict_types = 1);

namespace Drupal\sendgrid_api;

use Drupal\Core\Config\ConfigFactoryInterface;
use GuzzleHttp\ClientInterface;
use SendGrid as SendGridOriginal;

/**
 * Factory for creating a SendGrid client instance.
 */
class SendGridFactory {

  /**
   * Service for managing API key.
   *
   * @var \Drupal\sendgrid_api\SendGridApiKeyInterface
   */
  protected $sendGridApiKey;

  /**
   * Configuration factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * HTTP client.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  protected $httpClient;

  /**
   * SendGridFactory constructor.
   *
   * @param \Drupal\sendgrid_api\SendGridApiKeyInterface $sendGridApiKey
   *   Service for managing API key.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   Configuration factory.
   * @param \GuzzleHttp\ClientInterface $httpClient
   *   HTTP client.
   */
  public function __construct(SendGridApiKeyInterface $sendGridApiKey, ConfigFactoryInterface $configFactory, ClientInterface $httpClient) {
    $this->sendGridApiKey = $sendGridApiKey;
    $this->configFactory = $configFactory;
    $this->httpClient = $httpClient;
  }

  /**
   * Creates a new SendGrid client instance with site credentials.
   *
   * @return \SendGrid
   *   A SendGrid client instance.
   *
   * @throws \Drupal\sendgrid_api\Exception\SendGridApiExceptionInterface
   *   When the client could not be created.
   */
  public function createInstance(): SendGridOriginal {
    $apiKey = $this->sendGridApiKey->getApiKey();

    $options = [];
    if ($this->configFactory->get('sendgrid_api.settings')->get('http_client_shim')) {
      $options['guzzle'] = $this->httpClient;
    }

    return new SendGrid($apiKey, $options);
  }

}
