<?php

namespace Drupal\colorbox_field_formatter\Plugin\Field\FieldFormatter;

use Drupal\colorbox\ColorboxAttachment;
use Drupal\Component\Utility\Html;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Url;
use Drupal\Core\Utility\Token;
use Drupal\field_ui\Form\EntityViewDisplayEditForm;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the 'colorbox_field_formatter' formatter.
 *
 * @FieldFormatter(
 *   id = "colorbox_field_formatter",
 *   label = @Translation("Colorbox FF"),
 *   field_types = {
 *     "computed",
 *     "string"
 *   }
 * )
 */
class ColorboxFieldFormatter extends FormatterBase implements ContainerFactoryPluginInterface {

  /**
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * @var \Drupal\colorbox\ColorboxAttachment
   */
  protected $colorboxAttachment;

  /**
   * @var \Drupal\Core\Utility\Token
   */
  protected $token;

  /**
   * ColorboxFieldFormatter constructor.
   *
   * @param $plugin_id
   * @param $plugin_definition
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   * @param array $settings
   * @param $label
   * @param $view_mode
   * @param array $third_party_settings
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   * @param \Drupal\colorbox\ColorboxAttachment $colorbox_attachment
   * @param \Drupal\Core\Utility\Token $token
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, $label, $view_mode, array $third_party_settings, ModuleHandlerInterface $module_handler, ColorboxAttachment $colorbox_attachment, Token $token) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $label, $view_mode, $third_party_settings);
    $this->moduleHandler = $module_handler;
    $this->colorboxAttachment = $colorbox_attachment;
    $this->token = $token;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['label'],
      $configuration['view_mode'],
      $configuration['third_party_settings'],
      $container->get('module_handler'),
      $container->get('colorbox.attachment'),
      $container->get('token')
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'style' => 'default',
      'link_type' => 'content',
      'link' => '',
      'width' => '500',
      'height' => '500',
      'iframe' => 0,
      'inline_selector' => '',
      'anchor' => '',
      'class' => '',
      'rel' => '',
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $form = parent::settingsForm($form, $form_state);

    $form['style'] = [
      '#title' => $this->t('Style of colorbox'),
      '#type' => 'select',
      '#default_value' => $this->getSetting('style'),
      '#options' => $this->getStyles(),
      '#attributes' => [
        'class' => ['colorbox-field-formatter-style'],
      ],
    ];

    $form['link_type'] = [
      '#title' => $this->t('Link colorbox to'),
      '#type' => 'select',
      '#default_value' => $this->getSetting('link_type'),
      '#options' => $this->getLinkTypes(),
      '#attributes' => [
        'class' => ['colorbox-field-formatter-link-type'],
      ],
      '#states' => [
        'visible' => [
          'select.colorbox-field-formatter-style' => ['value' => 'default'],
        ],
      ],
    ];
    $form['link'] = [
      '#title' => $this->t('URI'),
      '#type' => 'textfield',
      '#default_value' => $this->getSetting('link'),
      '#states' => [
        'visible' => [
          'select.colorbox-field-formatter-style' => ['value' => 'default'],
          'select.colorbox-field-formatter-link-type' => ['value' => 'manual'],
        ],
      ],
    ];
    if ($this->moduleHandler->moduleExists('token') &&
      ($buildInfo = $form_state->getBuildInfo()) &&
      ($callback_object = $buildInfo['callback_object']) &&
      ($callback_object instanceof EntityViewDisplayEditForm)) {
      $form['token_help_wrapper'] = [
        '#type' => 'container',
        '#states' => [
          'visible' => [
            'select.colorbox-field-formatter-style' => ['value' => 'default'],
            'select.colorbox-field-formatter-link-type' => ['value' => 'manual'],
          ],
        ],
      ];
      $form['token_help_wrapper']['token_help'] = [
        '#theme' => 'token_tree_link',
        '#token_types' => ['entity' => $callback_object->getEntity()->getTargetEntityTypeId()],
        '#global_types' => TRUE,
      ];
    }

    $form['inline_selector'] = [
      '#title' => $this->t('Inline selector'),
      '#type' => 'textfield',
      '#default_value' => $this->getSetting('inline_selector'),
      '#states' => [
        'visible' => [
          'select.colorbox-field-formatter-style' => ['value' => 'colorbox-inline'],
        ],
      ],
    ];

    $form['width'] = [
      '#title' => $this->t('Width'),
      '#type' => 'textfield',
      '#default_value' => $this->getSetting('width'),
    ];
    $form['height'] = [
      '#title' => $this->t('Height'),
      '#type' => 'textfield',
      '#default_value' => $this->getSetting('height'),
    ];
    $form['iframe'] = [
      '#title' => $this->t('iFrame Mode'),
      '#type' => 'checkbox',
      '#default_value' => $this->getSetting('iframe'),
    ];
    $form['anchor'] = [
      '#title' => $this->t('Anchor'),
      '#type' => 'textfield',
      '#default_value' => $this->getSetting('anchor'),
    ];
    $form['class'] = [
      '#title' => $this->t('Class'),
      '#type' => 'textfield',
      '#default_value' => $this->getSetting('class'),
    ];
    $form['rel'] = [
      '#title' => $this->t('Rel'),
      '#type' => 'textfield',
      '#default_value' => $this->getSetting('rel'),
      '#description' => $this->t('This can be used to identify a group for Colorbox to cycle through.'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];

    $styles = $this->getStyles();
    $summary[] = $this->t('Style: @style', ['@style' => $styles[$this->getSetting('style')],]);

    if ($this->getSetting('style') === 'default') {
      $types = $this->getLinkTypes();
      if ($this->getSetting('link_type') === 'manual') {
        $summary[] = $this->t('Link to @link', ['@link' => $this->getSetting('link'),]);
      }
      else {
        $summary[] = $this->t('Link to @link', ['@link' => $types[$this->getSetting('link_type')],]);
      }
    }

    if ($this->getSetting('style') === 'colorbox-inline') {
      $summary[] = $this->t('Inline selector: @selector', ['@selector' => $this->getSetting('inline_selector'),]);
    }
    $summary[] = $this->t('Width: @width', ['@width' => $this->getSetting('width'),]);
    $summary[] = $this->t('Height: @height', ['@height' => $this->getSetting('height'),]);
    $summary[] = $this->t('iFrame Mode: @mode', ['@mode' => ($this->getSetting('iframe') ? $this->t('Yes') : $this->t('No')),]);
    if (!empty($this->getSetting('anchor'))) {
      $summary[] = $this->t('Anchor: #@anchor', ['@anchor' => $this->getSetting('anchor'),]);
    }
    if (!empty($this->getSetting('class'))) {
      $summary[] = $this->t('Classes: @class', ['@class' => $this->getSetting('class'),]);
    }
    if (!empty($this->getSetting('rel'))) {
      $summary[] = $this->t('Rel: @rel', ['@rel' => $this->getSetting('rel')]);
    }

    return $summary;
  }

  /**
   * {@inheritdoc}
   * @throws \Drupal\Core\Entity\EntityMalformedException
   */
  public function viewElements(FieldItemListInterface $items, $langcode): array {
    $element = [];

    foreach ($items as $delta => $item) {
      $output = $this->viewValue($item);
      $url = $this->getUrl($item);
      $options = [
        'html' => TRUE,
        'attributes' => [
          'class' => ['colorbox', $this->getSetting('style')],
        ],
        'query' => [
          'width' => $this->getSetting('width'),
          'height' => $this->getSetting('height'),
        ],
      ];
      if ($this->getSetting('iframe')) {
        $options['query']['iframe'] = 'true';
      }
      if (!empty($this->getSetting('anchor'))) {
        $options['fragment'] = $this->getSetting('anchor');
      }
      if (!empty($this->getSetting('class'))) {
        $options['attributes']['class'] = array_merge($options['attributes']['class'], explode(' ', $this->getSetting('class')));
      }
      if (!empty($this->getSetting('rel'))) {
        $options['attributes']['rel'] = $this->getSetting('rel');
      }
      if ($this->getSetting('style') === 'colorbox-inline') {
        $colorbox_inline_attributes = [
          'data-colorbox-inline' => $this->getSetting('inline_selector'),
          'data-width' => $this->getSetting('width'),
          'data-height' => $this->getSetting('height'),
        ];
        $options['attributes'] = array_merge($options['attributes'], $colorbox_inline_attributes);
      }

      $url->setOptions($options);
      $link = Link::fromTextAndUrl($output, $url);
      $element[$delta] = $link->toRenderable();
    }

    // Attach the Colorbox JS and CSS.
    if ($this->colorboxAttachment->isApplicable()) {
      $this->colorboxAttachment->attach($element);
    }

    return $element;
  }

  /**
   * Generate the output appropriate for one field item.
   *
   * @param \Drupal\Core\Field\FieldItemInterface $item
   *   One field item.
   *
   * @return string|array
   *   The textual output generated.
   */
  protected function viewValue(FieldItemInterface $item) {
    // The text value has no text format assigned to it, so the user input
    // should equal the output, including newlines.
    /** @noinspection PhpUndefinedFieldInspection */
    return nl2br(Html::escape($item->value));
  }

  /**
   * @param \Drupal\Core\Field\FieldItemInterface $item
   *   One field item.
   *
   * @return \Drupal\Core\Url
   * @throws \Drupal\Core\Entity\EntityMalformedException
   */
  protected function getUrl(FieldItemInterface $item): Url {
    $entity = $item->getEntity();
    if ($this->getSetting('link_type') === 'content') {
      return $entity->toUrl();
    }

    $link = $this->getSetting('link');
    if ($this->moduleHandler->moduleExists('token')) {
      $link = $this->token->replace($this->getSetting('link'), [$entity->getEntityTypeId() => $entity], ['clear' => TRUE]);
    }
    return Url::fromUserInput($link);
  }

  /**
   * Callback to provide an array for a select field containing all supported
   * colorbox styles.
   *
   * @return array
   */
  private function getStyles(): array {
    $styles = [
      'default' => $this->t('Default'),
    ];
    if ($this->moduleHandler->moduleExists('colorbox_inline')) {
      $styles['colorbox-inline'] = $this->t('Colorbox inline');
    }
    if ($this->moduleHandler->moduleExists('colorbox_node')) {
      $styles['colorbox-node'] = $this->t('Colorbox node');
    }

    return $styles;
  }

  /**
   * Callback to provide an arry for a select field containing all link types.
   *
   * @return array
   */
  private function getLinkTypes(): array {
    return [
      'content' => $this->t('Content'),
      'manual' => $this->t('Manually provide a link'),
    ];
  }

}
