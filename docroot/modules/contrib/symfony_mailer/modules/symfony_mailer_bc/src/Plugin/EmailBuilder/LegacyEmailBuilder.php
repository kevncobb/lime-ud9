<?php

namespace Drupal\symfony_mailer_bc\Plugin\EmailBuilder;

use Drupal\Component\Render\MarkupInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\symfony_mailer\EmailFactoryInterface;
use Drupal\symfony_mailer\EmailInterface;
use Drupal\symfony_mailer\Exception\SkipMailException;
use Drupal\symfony_mailer\MailerHelperInterface;
use Drupal\symfony_mailer\Processor\EmailBuilderBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines the Legacy Email Builder plug-in that calls hook_mail().
 */
class LegacyEmailBuilder extends EmailBuilderBase implements ContainerFactoryPluginInterface {

  /**
   * The mailer helper.
   *
   * @var \Drupal\symfony_mailer\MailerHelperInterface
   */
  protected $mailerHelper;

  /**
   * List of headers for conversion from array.
   *
   * We omitted the To header, since it needs to be set in the build phase.
   *
   * @var array
   */
  protected const HEADERS = [
    'From' => 'from',
    'Reply-To' => 'reply-to',
    'Cc' => 'cc',
    'Bcc' => 'bcc',
  ];

  /**
   * Constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\symfony_mailer\MailerHelperInterface $mailer_helper
   *   The mailer helper.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, MailerHelperInterface $mailer_helper) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->mailerHelper = $mailer_helper;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('symfony_mailer.helper')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function fromArray(EmailFactoryInterface $factory, array $message) {
    return $factory->newTypedEmail($message['module'], $message['key'], $message);
  }

  /**
   * {@inheritdoc}
   */
  public function createParams(EmailInterface $email, array $legacy_message = NULL) {
    assert($legacy_message != NULL);
    $email->setParam('legacy_message', $legacy_message);
  }

  /**
   * {@inheritdoc}
   */
  public function build(EmailInterface $email) {
    $message = $email->getParam('legacy_message');
    $recipient = $message['to'] ?? NULL;
    if (isset($recipient)) {
      $email->setTo($this->mailerHelper->parseAddress($recipient, $message['langcode']));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function preRender(EmailInterface $email) {
    $message = $email->getParam('legacy_message');
    $message += [
      'subject' => '',
      'body' => [],
      'headers' => [],
    ];

    // Call hook_mail() on this module.
    if (function_exists($function = $message['module'] . '_mail')) {
      $function($message['key'], $message, $message['params']);
    }

    if (isset($message['send']) && !$message['send']) {
      throw new SkipMailException('Send aborted by hook_mail().');
    }

    $this->emailFromArray($email, $message);
  }

  /**
   * Fills an Email from a message array.
   *
   * @param \Drupal\symfony_mailer\EmailInterface $email
   *   The email to fill.
   * @param array $message
   *   The array to fill from.
   */
  protected function emailFromArray(EmailInterface $email, array $message) {
    $email->setSubject($message['subject']);

    // Add attachments.
    $attachments = $message['params']['attachments'] ?? [];
    foreach ($attachments as $attachment) {
      $email->attachFromPath($attachment['filepath'], $attachment['filename'] ?? NULL, $attachment['filemime'] ?? NULL);
    }

    // Add Address headers from message array to Email object.
    // The "To" header will be set via build().
    foreach (self::HEADERS as $name => $key) {
      $encoded = $message['headers'][$name] ?? $message[$key] ?? NULL;
      if (isset($encoded)) {
        $email->setAddress($name, $this->mailerHelper->parseAddress($encoded));
      }
    }

    // Add the body to the Email object, as rendered by hook_mail.
    foreach ($message['body'] as $part) {
      if ($part instanceof MarkupInterface) {
        $body[] = ['#markup' => $part];
      }
      else {
        $body[] = [
          '#type' => 'processed_text',
          '#text' => $part,
        ];
      }
    }
    $email->setBody($body ?? []);
  }

}
