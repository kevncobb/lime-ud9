<?php

namespace Drupal\symfony_mailer_bc_test\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Mail\MailManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Test module form to send a test BC mail.
 */
class BcTestMailForm extends FormBase {

  /**
   * The mail manager service.
   *
   * @var \Drupal\Core\Mail\MailManagerInterface
   */
  protected $mailManager;

  /**
   * Constructs TestMailForm.
   *
   * @param \Drupal\Core\Mail\MailManagerInterface $mail_manager
   *   The mail manager service.
   */
  public function __construct(MailManagerInterface $mail_manager) {
    $this->mailManager = $mail_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.mail')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'symfony_mailer_bc_test_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = [];
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => 'Send test mail',
    ];
    /** @var \Drupal\Core\Theme\ThemeManagerInterface $theme_manager */
    $theme_manager = \Drupal::service('theme.manager');
    $current_theme = $theme_manager->getActiveTheme()->getName();
    $form['current_theme'] = [
      '#markup' => 'Current theme: ' . $current_theme,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->mailManager->mail('symfony_mailer_bc_test', 'bc_test', 'test@example.com', 'en');
  }

}
