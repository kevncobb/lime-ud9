<?php

namespace Drupal\juicerio\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Block\BlockPluginInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a Juicer embed block.
 *
 * @Block(
 *   id = "juicerio",
 *   admin_label = @Translation("Juicer Embed Feed"),
 *   category = @Translation("Juicer"),
 *   deriver = "Drupal\juicerio\Plugin\Derivative\JuicerEmbedFeedBlockDerivative"
 * )
 */
class JuicerEmbedFeedBlock extends BlockBase implements BlockPluginInterface, ContainerFactoryPluginInterface {

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Constructs a global locator.
   *
   * @param array $configuration
   *   Plugin configuration.
   * @param string $plugin_id
   *   The plugin ID.
   * @param mixed $plugin_definition
   *   The plugin definition.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The Drupal config factory service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ConfigFactoryInterface $config_factory) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('config.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $delta = $this->getDerivativeId();
    $config = $this->getConfiguration();

    // Options array for later down in the form.
    $post_number_array = [
      1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 15,
      20, 25, 50, 75, 100, 250, 500, 1000,
    ];
    $pagination_array = [
      0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 15, 20, 25, 50, 100,
    ];
    $column_array = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12];
    $style_array = [
      'modern' => 'Modern',
      'night' => 'Night',
      'polaroid' => 'Polaroid',
      'image_grid' => 'Image Grid',
      'widget' => 'Widget',
      'slider' => 'Slider',
      'hip' => 'Hip',
      'living_wall' => 'Living Wall',
    ];

    $form['juicer'] = [
      '#type' => 'details',
      '#title' => 'Juicer Feed Settings',
    ];

    // Set the Juicer ID at the block level.
    $form['juicer'][$delta . '_feed_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Juicer Username Override'),
      '#default_value' => isset($config[$delta . '_feed_id']) ? $config[$delta . '_feed_id'] : '',
      '#description' => $this->t('Enter a block level Juicer username. If left blank the default will be taken from the global username.'),
    ];

    // Feed style.
    $form['juicer'][$delta . '_style'] = [
      '#type' => 'select',
      '#title' => $this->t('Feed Style'),
      '#options' => $style_array,
      '#description' => $this->t('Select the style in which the feed is displayed.'),
      '#default_value' => isset($config[$delta . '_style']) ? $config[$delta . '_style'] : 'modern',
    ];

    // Number of posts.
    $form['juicer'][$delta . '_post_number'] = [
      '#type' => 'select',
      '#title' => $this->t('Number of posts'),
      '#options' => array_combine($post_number_array, $post_number_array),
      '#description' => $this->t('Set the total number of posts shown. Defaults to 100.'),
      '#default_value' => isset($config[$delta . '_post_number']) ? $config[$delta . '_post_number'] : 0,
    ];

    // Infinite scrolling pages.
    $form['juicer'][$delta . '_infinite_pages'] = [
      '#type' => 'select',
      '#title' => $this->t('Number of scrolling pages'),
      '#default_value' => isset($config[$delta . '_infinite_pages']) ? $config[$delta . '_infinite_pages'] : 0,
      '#options' => array_combine($pagination_array, $pagination_array),
      '#description' => $this->t('Set to 0 your feed will scroll infinitely: more and more posts will keep being added to the feed until all your posts are visible.<br />
      If set to 1, only the first page of results will be visible. If set to 2, the feed will scroll just once.'),
    ];

    // Space between posts.
    $form['juicer'][$delta . '_gutter_amt'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Column gutter size'),
      '#field_suffix' => $this->t('pixels'),
      '#size' => 5,
      '#default_value' => isset($config[$delta . '_gutter_amt']) ? $config[$delta . '_gutter_amt'] : '',
      '#description' => $this->t('The column gutter is the horizontal space between columns of posts. Defaults to 20 pixels.'),
    ];

    // Change number of columns.
    $form['juicer'][$delta . '_column_number'] = [
      '#type' => 'select',
      '#title' => $this->t('Number of columns'),
      '#default_value' => isset($config[$delta . '_column_number']) ? $config[$delta . '_column_number'] : 1,
      '#options' => array_combine($column_array, $column_array),
      '#description' => $this->t("Columns are not allowed to be less than 200px (as it doesn't look good). If the number of columns set here is not respected, increase the size of the containing element."),
    ];

    // Filter the posts based on social account.
    $form['juicer'][$delta . '_filter'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Filter the posts based on social account'),
      '#default_value' => isset($config[$delta . '_filter']) ? $config[$delta . '_filter'] : '',
      '#description' => $this->t('To filter your posts, enter either the capitalized name of the source, or the account name of source source.<br />
       Example: If you have an Instagram source of #tbt, enter either <em>tbt</em> or <em>Instagram</em> to only show posts from that source.<br />
       Note: If you have multiple Instagram sources entering <em>Instagram</em> will show posts from all of them.'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    parent::blockSubmit($form, $form_state);
    $values = $form_state->getValues();
    $delta = $this->getDerivativeId();
    $this->configuration[$delta . '_feed_id'] = $values['juicer'][$delta . '_feed_id'];
    $this->configuration[$delta . '_post_number'] = $values['juicer'][$delta . '_post_number'];
    $this->configuration[$delta . '_infinite_pages'] = $values['juicer'][$delta . '_infinite_pages'];
    $this->configuration[$delta . '_gutter_amt'] = $values['juicer'][$delta . '_gutter_amt'];
    $this->configuration[$delta . '_column_number'] = $values['juicer'][$delta . '_column_number'];
    $this->configuration[$delta . '_filter'] = $values['juicer'][$delta . '_filter'];
    $this->configuration[$delta . '_style'] = $values['juicer'][$delta . '_style'];
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $delta = $this->getDerivativeId();
    if ($this->configuration[$delta . '_feed_id']) {
      $juicer_feed_id = $this->configuration[$delta . '_feed_id'];
    }
    else {
      $juicer_feed_id = $this->configFactory->get('juicerio.settings')->get('juicer_feed_id');
    }
    // Permit the alternative JS library to be attached here [#2917855].
    $juicer_library = $this->configFactory->get('juicerio.settings')->get('juicer_js_embed');

    return [
      '#theme' => 'juicerio_feed',
      '#feed_id' => $juicer_feed_id,
      '#post_num' => $this->configuration[$delta . '_post_number'],
      '#infinite_pages' => $this->configuration[$delta . '_infinite_pages'],
      '#gutter_amt' => $this->configuration[$delta . '_gutter_amt'],
      '#column_num' => $this->configuration[$delta . '_column_number'],
      '#filters' => $this->configuration[$delta . '_filter'],
      '#attached' => [
        'library' => [
          'juicerio/' . $juicer_library,
          'juicerio/juicerio.styles',
        ],
      ],
    ];
  }

}
