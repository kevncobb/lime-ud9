<?php

declare(strict_types = 1);

namespace Drupal\sendgrid_api\Plugin\KeyType;

use Drupal\Core\Form\FormStateInterface;
use Drupal\key\Plugin\KeyTypeBase;

/**
 * Defines a key for SendGrid API key.
 *
 * @KeyType(
 *   id = "sendgrid_api_key",
 *   label = @Translation("SendGrid"),
 *   description = @Translation("SendGrid API Key"),
 *   group = "authentication",
 *   key_value = {
 *     "plugin" = "text_field"
 *   }
 * )
 */
class SendGridKeyType extends KeyTypeBase {

  /**
   * {@inheritdoc}
   */
  public static function generateKeyValue(array $configuration) {
    $sample1 = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $sample2 = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890_-';

    $result = '';
    for ($i = 0; $i < 2; $i++) {
      $result .= substr(str_shuffle($sample1), 0, 1);
    }
    $result .= '.';
    for ($i = 0; $i < 22; $i++) {
      $result .= substr(str_shuffle($sample2), 0, 1);
    }
    $result .= '.';
    for ($i = 0; $i < 43; $i++) {
      $result .= substr(str_shuffle($sample2), 0, 1);
    }

    return $result;
  }

  /**
   * {@inheritdoc}
   */
  public function validateKeyValue(array $form, FormStateInterface $form_state, $key_value) {
    $valid = preg_match('/^[A-Z]{2}\.[a-zA-Z0-9\-_]{22}\.[a-zA-Z0-9\-_]{43}$/', $key_value) === 1;
    if (!$valid) {
      $form_state->setError($form['settings']['input_section']['key_input_settings']['key_value'], $this->t('API key is not in expected format.'));
    }
  }

}
