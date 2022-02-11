<?php

namespace Drupal\symfony_mailer;

use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email as SymfonyEmail;
use Symfony\Component\Mime\Header\Headers;

/**
 * Trait that implements BaseEmailInterface, writing to a Symfony Email object.
 */
trait BaseEmailTrait {

  /**
   * The inner Symfony Email object.
   *
   * @var \Symfony\Component\Mime\Email
   */
  protected $inner;

  /**
   * The email subject.
   *
   * @var \Drupal\Component\Render\MarkupInterface|string
   */
  protected $subject;

  public function setSubject($subject) {
    // We must not force conversion of the subject to a string as this could
    // cause translation before switching to the correct language.
    $this->subject = $subject;
    return $this;
  }

  public function getSubject() {
    return $this->subject;
  }

  public function setSender($address) {
    $this->inner->sender($address);
    return $this;
  }

  public function getSender(): ?Address {
    return $this->inner->getSender();
  }

  public function addFrom(...$addresses) {
    $this->inner->addFrom(...$addresses);
    return $this;
  }

  public function setFrom(...$addresses) {
    $this->inner->from(...$addresses);
    return $this;
  }

  public function getFrom(): array {
    return $this->inner->getFrom();
  }

  public function addReplyTo(...$addresses) {
    $this->inner->addReplyTo(...$addresses);
    return $this;
  }

  public function setReplyTo(...$addresses) {
    $this->inner->replyTo(...$addresses);
    return $this;
  }

  public function getReplyTo(): array {
    return $this->inner->getReplyTo();
  }

  public function addTo(...$addresses) {
    $this->inner->addTo(...$addresses);
    return $this;
  }

  public function setTo(...$addresses) {
    $this->inner->to(...$addresses);
    return $this;
  }

  public function getTo(): array {
    return $this->inner->getTo();
  }

  public function addCc(...$addresses) {
    $this->inner->addCc(...$addresses);
    return $this;
  }

  public function setCc(...$addresses) {
    $this->inner->cc(...$addresses);
    return $this;
  }

  public function getCc(): array {
    return $this->inner->getCc();
  }

  public function addBcc(...$addresses) {
    $this->inner->addBcc(...$addresses);
    return $this;
  }

  public function setBcc(...$addresses) {
    $this->inner->bcc(...$addresses);
    return $this;
  }

  public function getBcc(): array {
    return $this->inner->getBcc();
  }

  public function setPriority(int $priority) {
    $this->inner->priority($priority);
    return $this;
  }

  public function getPriority(): int {
    return $this->inner->getPriority();
  }

  public function setTextBody(string $body) {
    $this->inner->text($body);
    return $this;
  }

  public function getTextBody(): ?string {
    return $this->inner->getTextBody();
  }

  public function setHtmlBody(?string $body) {
    $this->valid('postRender');
    $this->inner->html($body);
    return $this;
  }

  public function getHtmlBody(): ?string {
    $this->valid('postRender');
    return $this->inner->getHtmlBody();
  }

  // public function attach(string $body, string $name = null, string $contentType = null);

  // public function attachFromPath(string $path, string $name = null, string $contentType = null);

  // public function embed(string $body, string $name = null, string $contentType = null);

  // public function embedFromPath(string $path, string $name = null, string $contentType = null);

  // public function attachPart(DataPart $part);

  // public function getAttachments();

  public function getHeaders(): Headers {
    return $this->inner->getHeaders();
  }

  public function addTextHeader(string $name, string $value) {
    $this->getHeaders()->addTextHeader($name, $value);
    return $this;
  }

}
