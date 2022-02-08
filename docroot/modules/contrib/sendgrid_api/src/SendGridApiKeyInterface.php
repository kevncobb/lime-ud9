<?php

declare(strict_types=1);

namespace Drupal\sendgrid_api;

/**
 * Interface for managing a SendGrid API key.
 */
interface SendGridApiKeyInterface {

  /**
   * Get the SendGrid API key.
   *
   * @return string
   *   The SendGrid API key.
   *
   * @throws \Drupal\sendgrid_api\Exception\SendGridApiInvalidKeyException
   *   If API key configuration is invalid. Does not validate whether key is
   *   actually valid when used with the API.
   */
  public function getApiKey(): string;

}
