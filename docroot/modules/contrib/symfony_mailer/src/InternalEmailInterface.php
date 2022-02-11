<?php

namespace Drupal\symfony_mailer;

/**
 * Defines an extended Email interface that adds internal functions.
 */
interface InternalEmailInterface extends EmailInterface {

  /**
   * Call a function for all email processors.
   *
   * Valid: postRender after rendering else before building.
   *
   * @internal
   *
   * @param string $function
   *   The function to call: preBuild, preRender or postRender.
   *
   * @return $this
   */
  public function process(string $function);

  /**
   * Renders the email.
   *
   * Valid: before rendering.
   *
   * @internal
   *
   * @return $this
   */
  public function render();

  /**
   * Gets the inner Symfony email to send.
   *
   * Valid: after rendering.
   *
   * @internal
   *
   * @return \Symfony\Component\Mime\Email
   *   Inner Symfony email.
   */
  public function getSymfonyEmail();

}