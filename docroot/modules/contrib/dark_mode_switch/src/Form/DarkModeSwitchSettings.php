<?php

namespace Drupal\dark_mode_switch\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Dark mode switch settings form.
 */
class DarkModeSwitchSettings extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */

  protected function getEditableConfigNames() {
    return [
      'dark_mode_switch.settings',
    ];
  }

  public function getFormId() {
    return 'dark_mode_switch_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('dark_mode_switch.settings');

    $form = [];

    $form['dark_class'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Dark mode classname to append'),
      '#description' => $this->t('Enter the classname to append to the parent element when dark mode is enabled.'),
      '#required' => TRUE,
      '#default_value' => $config->get('dark_class'),
    ];

    $form['parent_element'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Parent element to bind to'),
      '#description' => $this->t('The jQuery element selector you want the dark mode class to appear on. This could be an element, like body or html, or a class selector like .content.'),
      '#required' => TRUE,
      '#default_value' => $config->get('parent_element'),
    ];

    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save configuration'),
      '#button_type' => 'primary',
    ];

    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $this->config('dark_mode_switch.settings')
      ->set('dark_class', $form_state->getValue('dark_class'))
      ->set('parent_element', $form_state->getValue('parent_element'))
      ->save();

    $this->messenger()->addMessage($this->t('Configuration saved.'));
  }

}
