<?php

namespace Drupal\feedback\Plugin\Block;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Path\CurrentPathStack;

/**
 * Provides a 'FeedbackBlock' block.
 *
 * @Block(
 *  id = "feedback_block",
 *  admin_label = @Translation("Feedback block"),
 * )
 */
class FeedbackBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Drupal\Core\Path\CurrentPathStack definition.
   *
   * @var \Drupal\Core\Path\CurrentPathStack
   */
  protected $path;

  /**
   * The bundle info service to know which feedback types exist.
   *
   * @var \Drupal\Core\Entity\EntityTypeBundleInfoInterface
   */
  protected $bundleInfo;

  /**
   * Construct.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param string $plugin_definition
   *   The plugin implementation definition.
   */
  public function __construct(
        array $configuration,
        $plugin_id,
        $plugin_definition,
        CurrentPathStack $path_current,
        EntityTypeBundleInfoInterface $bundle_info
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->path = $path_current;
    $this->bundleInfo = $bundle_info;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('path.current'),
      $container->get('entity_type.bundle.info')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];

    $build['feedback'] = [
      '#type' => 'details',
      '#title' => $this->t('@title', ['@title' => $this->configuration['label']]),
    ];
    $build['feedback']['feedback_help'] = [
      '#markup' => $this->configuration['feedback_help'],
    ];
    $build['feedback']['feedback_form'] = [
      '#lazy_builder' => ['feedback.lazy_builders:renderForm',
        [
          $this->configuration['feedback_type'],
          'internal:' . $this->path->getPath(),
          \Drupal::request()->getQueryString(),
          $this->configuration['feedback_submit'],
        ],
      ],
      '#create_placeholder' => TRUE,
    ];

    $build['#attached'] = [
      'library' => [
        'feedback/feedback',
      ],
    ];

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  protected function blockAccess(AccountInterface $account) {
    return AccessResult::allowedIfHasPermission($account, 'add feedback message entities');
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $type = isset($this->configuration['feedback_type']) ? $this->configuration['feedback_type'] : '';
    $message = $this->t('If you experience a bug or would like to see an addition on the current page, feel free to leave us a message.');
    $message = isset($this->configuration['feedback_help']) ? $this->configuration['feedback_help'] : $message;
    $submit = isset($this->configuration['feedback_submit']) ? $this->configuration['feedback_submit'] : $this->t('Send');

    $feedback_types = array_map(function ($item) {
      return $item['label'];
    }, $this->bundleInfo->getBundleInfo('feedback_message'));

    $form['feedback_type'] = [
      '#type' => 'select',
      '#title' => $this->t('Feedback type'),
      '#options' => $feedback_types,
      '#default_option' => $type,
      '#description' => $this->t('Select the feedback type which will be used.'),
    ];

    $form['feedback_help'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Help'),
      '#default_value' => $message,
      '#description' => $this->t('Feedback submission guidelines.'),
    ];

    $form['feedback_submit'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Submit button'),
      '#default_value' => $submit,
      '#description' => $this->t('Specify the label that will appear on the feedback submit button.'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['feedback_type'] = $form_state->getValue('feedback_type');
    $this->configuration['feedback_help'] = $form_state->getValue('feedback_help');
    $this->configuration['feedback_submit'] = $form_state->getValue('feedback_submit');
  }

}
