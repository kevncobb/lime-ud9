<?php

namespace Drupal\views_role_based_global_text;

use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Plugin\views\area\Text;

/**
 * Class RoleBasedGlobalText.
 */
class RoleBasedGlobalText extends Text {

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();
    $options['roles_fieldset']['default'] = FALSE;
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);

    $form['roles_fieldset'] = [
      '#type'  => 'details',
      '#title' => $this->t('Roles'),
    ];
    $form['roles_fieldset']['roles'] = [
      '#title' => $this->t('Select Roles'),
      '#type' => 'checkboxes',
      '#options' => user_role_names(),
      '#default_value' => $this->options['roles_fieldset']['roles'] ?? [],
      '#description' => $this->t('Only the checked roles will be able to access this value. If no role is selected, available to all.'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function render($empty = FALSE) {
    // Get the checked roles.
    $checked_roles = $this->options['roles_fieldset'] && is_array($this->options['roles_fieldset']['roles']) ? array_filter($this->options['roles_fieldset']['roles']) : [];

    // Roles assigned to logged-in users.
    $user_roles = \Drupal::currentUser()->getRoles();

    if (empty($checked_roles) || array_intersect($user_roles, $checked_roles)) {
      return parent::render($empty);
    }

    return [];
  }

}
