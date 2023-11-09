<?php

namespace Drupal\l10n_client_ui\Ajax;

use Drupal\Core\Ajax\CommandInterface;

/**
 * Class SaveTranslationCommand.
 *
 * Invokes a client function, which is responsible for saving a new
 * translation in the internal array and updating the form interface.
 *
 * @package Drupal\l10n_client_ui\Ajax
 */
class SaveTranslationCommand implements CommandInterface {

  /**
   * A submit button which was used for saving the specified translation.
   *
   * @var string
   */
  protected $selector;

  /**
   * Constructs a SaveTranslationCommand object.
   *
   * @param string $selector
   *   A CSS selector for the submit button linked with specified translation
   *   string.
   */
  public function __construct(string $selector) {
    $this->selector = $selector;
  }

  /**
   * {@inheritdoc}
   */
  public function render(): array {
    return [
      'command' => 'saveTranslation',
      'selector' => $this->selector,
    ];
  }

}
