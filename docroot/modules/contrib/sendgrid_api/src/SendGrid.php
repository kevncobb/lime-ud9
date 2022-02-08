<?php

declare(strict_types = 1);

namespace Drupal\sendgrid_api;

use GuzzleHttp\ClientInterface;
use SendGrid as SendGridOriginal;

/**
 * Overrides SendGrid client adding Guzzle support.
 */
class SendGrid extends SendGridOriginal {

  /**
   * {@inheritdoc}
   */
  public function __construct($apiKey, $options = []) {
    parent::__construct($apiKey, $options);
    if (($options['guzzle'] ?? NULL) instanceof ClientInterface) {
      // Swap out the created client with one compatible with Guzzle.
      $this->client = SendGridClient::createFromClient($this->client, $options['guzzle']);
    }
  }

}
