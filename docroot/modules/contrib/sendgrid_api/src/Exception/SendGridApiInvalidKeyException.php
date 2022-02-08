<?php

declare(strict_types = 1);

namespace Drupal\sendgrid_api\Exception;

/**
 * Exception thrown when API key is missing or invalid.
 */
class SendGridApiInvalidKeyException extends \Exception implements SendGridApiExceptionInterface {

}
