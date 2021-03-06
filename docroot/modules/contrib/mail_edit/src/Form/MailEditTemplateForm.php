<?php

namespace Drupal\mail_edit\Form;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Edit an email template.
 */
class MailEditTemplateForm extends FormBase {

  /**
   * The module handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('module_handler'),
    );
  }

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler service.
   */
  public function __construct(ModuleHandlerInterface $module_handler) {
    $this->moduleHandler = $module_handler;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'mail_edit_template_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $id = NULL, $lang = NULL) {
    // Load the template for this object.
    $template = $this->getTemplate($id);

    $form['id'] = [
      '#type' => 'value',
      '#value' => $id,
    ];

    $form['description'] = [
      '#markup' => isset($template['description']) ? Xss::filter($template['description']) : '',
      '#access' => isset($template['description']),
    ];

    $form['config'] = [
      '#title' => $this->t('Config'),
      '#type' => 'textfield',
      '#default_value' => $template['config'],
      '#disabled' => TRUE,
    ];
    $form['email'] = [
      '#title' => $this->t('Email'),
      '#type' => 'textfield',
      '#default_value' => $template['name'],
      '#disabled' => TRUE,
    ];

    $form['message']['subject'] = [
      '#title' => $this->t('Subject'),
      '#type' => 'textfield',
      '#default_value' => $template['subject'],
      '#maxlength' => 180,
      '#required' => TRUE,
    ];

    $form['message']['body'] = [
      '#title' => $this->t('Email body'),
      '#type' => 'textarea',
      '#default_value' => $template['body'],
      '#required' => TRUE,
    ];

    // If the Token module is installed, show a link to the token tree.
    if ($this->moduleHandler->moduleExists('token')) {
      $module_name = $this->getModuleName($id);

      // Trigger hook_mail_edit_token_types(). The 'user' entity will always be
      // available.
      $tokens = ['user']
        + (array) $this->moduleHandler
          ->invoke($module_name, 'mail_edit_token_types', [$template['name']]);

      // Show a link to the token browser.
      $form['message']['token_tree'] = [
        '#theme' => 'token_tree_link',
        '#token_types' => $tokens,
        '#show_restricted' => TRUE,
        '#show_nested' => FALSE,
      ];
    }

    // @todo WYSIWYG support.
    // @todo Plaintext support.
    $form['actions'] = [
      '#type' => 'actions',
    ];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save'),
      '#weight' => 10,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Work out the template ID.
    $id = $form_state->getValue('id');

    // Get the config object for this template.
    $config_factory = $this->configFactory()
      ->getEditable($this->getConfigName($id));
    $name = $this->getEmailName($id);

    // Update the config object.
    $config_factory->set($name . '.subject', $form_state->getValue('subject'));
    $config_factory->set($name . '.body', $form_state->getValue('body'));
    $config_factory->save();

    $this->messenger()
      ->addMessage($this->t('Email "%mesg" has been updated.', ['%mesg' => $name]));
    $form_state->setRedirect('mail_edit.list');
  }

  /**
   * Extract the module's name from an email ID.
   *
   * @param string $id
   *   A string in the format 'MODULENAME.CONFIGNAME.TEMPLATENAME'.
   *
   * @return string
   *   The name of the module which specifies the email.
   */
  private function getModuleName($id) {
    $parts = explode('.', $id);
    return $parts[0];
  }

  /**
   * Extract the email's config object name from an email ID.
   *
   * @param string $id
   *   A string in the format 'MODULENAME.CONFIGNAME.TEMPLATENAME'.
   *
   * @return string
   *   The name of the config object, which will be the first two portions of
   *   the ID when split on the period character.
   */
  private function getConfigName($id) {
    $parts = explode('.', $id);
    return $parts[0] . '.' . $parts[1];
  }

  /**
   * Extract the email's config object name from an email ID.
   *
   * @param string $id
   *   A string in the format 'MODULENAME.CONFIGNAME.TEMPLATENAME'.
   *
   * @return \Drupal\Core\Config\Config
   *   A full config object.
   */
  private function getConfig($id) {
    return $this->config($this->getConfigName($id));
  }

  /**
   * Extract the name of the email item from an email ID.
   *
   * @param string $id
   *   A string in the format 'MODULENAME.CONFIGNAME.TEMPLATENAME'.
   *
   * @return string
   *   The email template name.
   */
  private function getEmailName($id) {
    $parts = explode('.', $id);
    unset($parts[0]);
    unset($parts[1]);
    return implode('.', $parts);
  }

  /**
   * Load an email template from a combination string.
   *
   * @param string $id
   *   A combination of the config entity's machine name and the email's name.
   *
   * @return array
   *   Will contain the following elements:
   *   - subject - The email's subject line.
   *   - body - The email's body text.
   *   - config - The name of the config object this was found in.
   *   - name - The name of email template.
   */
  private function getTemplate($id) {
    // Load the config entity.
    /** @var \Drupal\Core\Config\Config $config */
    $config = $this->getConfig($id);
    // The email structure's name.
    $template_name = $this->getEmailName($id);

    // If the config object was found, generate it.
    if (!empty($config)) {
      // Extract the specific config object that was requested.
      $template = $config->get($template_name);
    }

    // If the config object didn't exist or the template wasn't defined, try
    // checking to make sure it was defined via the hook. This will allow email
    // config objects to be dynamically generated but block someone from being
    // able to create random config objects.
    if (empty($config) || !isset($template)) {
      $module_name = $this->getModuleName($id);
      $config_name = $this->getConfigName($id);

      // Trigger hook_mail_edit_templates().
      $data = $this->moduleHandler->invoke($module_name, 'mail_edit_templates');
      if (!isset($data, $data[$config_name], $data[$config_name][$template_name])) {
        throw new NotFoundHttpException();
      }
    }

    // If the template wasn't loaded, or doesn't exist, create an empty one so
    // that it can be saved.
    if (empty($template) || !is_array($template)
      || !isset($template['subject']) || !isset($template['body'])) {
      $template = [
        'subject' => '',
        'body' => '',
      ];
    }

    $template['config'] = $config->getName();
    $template['name'] = $template_name;
    return $template;
  }

}
