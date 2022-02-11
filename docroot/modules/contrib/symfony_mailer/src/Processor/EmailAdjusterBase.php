<?php

namespace Drupal\symfony_mailer\Processor;

use Drupal\Core\Form\FormStateInterface;

class EmailAdjusterBase extends EmailProcessorBase implements EmailAdjusterInterface {

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function getLabel() {
    return $this->pluginDefinition['label'];
  }

}
