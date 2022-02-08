<?php

declare(strict_types = 1);

namespace Drupal\sendgrid_api;

use GuzzleHttp\ClientInterface;
use SendGrid\Client;
use SendGrid\Response;
use function GuzzleHttp\headers_from_lines;

/**
 * Overrides SendGrid client adding Guzzle support.
 */
class SendGridClient extends Client {

  /**
   * HTTP client.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  protected $httpClient;

  /**
   * Constructs a new client using an existing client as a base.
   *
   * @param \SendGrid\Client $client
   *   An existing client.
   * @param \GuzzleHttp\ClientInterface $httpClient
   *   HTTP client.
   *
   * @return \Drupal\sendgrid_api\SendGridClient
   *   A new SendGrid client using Guzzle.
   */
  public static function createFromClient(Client $client, ClientInterface $httpClient): SendGridClient {
    $instance = new static(
      $client->host,
      $client->headers,
      $client->version,
      $client->path,
      $client->curlOptions,
      $client->retryOnLimit,
    );
    $instance->setHttpClient($httpClient);
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function _($name = NULL) {
    $client = parent::_(...func_get_args());
    // Push Guzzle down the chain.
    $client->setHttpClient($this->httpClient);
    return $client;
  }

  /**
   * {@inheritdoc}
   */
  public function makeRequest($method, $url, $body = NULL, $headers = NULL, $retryOnLimit = FALSE) {
    $headers = $headers ?? [];

    // @todo implement $retryOnLimit.
    $options = [];

    // Body.
    // @see \SendGrid\Client::createCurlOptions().
    if (isset($body)) {
      $encodedBody = json_encode($body);
      $headers[] = 'Content-Type: application/json';
      $options['body'] = $encodedBody;
    }

    $headers = array_merge($this->headers, $headers);
    $options['headers'] = headers_from_lines($headers);

    $response = $this->httpClient->request($method, $url, $options);

    return new Response(
      $response->getStatusCode(),
      $response->getBody(),
      $response->getHeaders(),
    );
  }

  /**
   * Sets HTTP client.
   *
   * @param \GuzzleHttp\ClientInterface $httpClient
   *   HTTP client.
   *
   * @return $this
   *   Returns self for chaining.
   */
  protected function setHttpClient(ClientInterface $httpClient) {
    $this->httpClient = $httpClient;
    return $this;
  }

}
