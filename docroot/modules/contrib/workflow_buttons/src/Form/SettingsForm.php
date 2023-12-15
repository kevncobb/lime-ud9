<?php

namespace Drupal\workflow_buttons\Form;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Configure global workflow_buttons settings.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'workflow_buttons_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'workflow_buttons.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $field_type = NULL) {
    $config = $this->config('workflow_buttons.settings');

    $form['top_buttons'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Buttons at top of form'),
      '#default_value' => $config->get('display.top_buttons'),
      '#description' => $this->t('Additionally show the buttons at the top of the form, <em>in addition</em> to their usual location at the bottom.'),
    );

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('workflow_buttons.settings');
    $config->set('display.top_buttons', (bool) $form_state->getValue('top_buttons'));
    $config->save();

    parent::submitForm($form, $form_state);
  }

}
