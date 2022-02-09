<?php

namespace Drupal\feeds\Feeds\Parser;

use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Render\Element;
use Drupal\feeds\Plugin\Type\MappingPluginFormInterface;
use Drupal\feeds\Plugin\Type\Parser\ParserInterface;
use Drupal\feeds\Plugin\Type\PluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Base class for Feeds parsers.
 */
abstract class ParserBase extends PluginBase implements ParserInterface, MappingPluginFormInterface, ContainerFactoryPluginInterface {

  /**
   * The custom source plugin manager.
   *
   * @var \Drupal\Component\Plugin\PluginManagerInterface
   */
  protected $customSourcePluginManager;

  /**
   * Constructs a new ParserBase object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param array $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Component\Plugin\PluginManagerInterface $custom_source_plugin_manager
   *   The custom source plugin manager.
   */
  public function __construct(array $configuration, $plugin_id, array $plugin_definition, PluginManagerInterface $custom_source_plugin_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->customSourcePluginManager = $custom_source_plugin_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('plugin.manager.feeds.custom_source')
    );
  }

  /**
   * Returns the label for single source.
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup|null
   *   A translated string if the source has a special name. Null otherwise.
   */
  protected function configSourceLabel() {
    return NULL;
  }

  /**
   * Returns the description for single source.
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup|null
   *   A translated string if there's a description. Null otherwise.
   */
  protected function configSourceDescription() {
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function mappingFormAlter(array &$form, FormStateInterface $form_state) {
    // Add the appropriate new custom source options to the select source
    // dropdown.
    foreach (Element::children($form['mappings']) as $i) {
      if (!isset($form['mappings'][$i]['map'])) {
        continue;
      }
      foreach (Element::children($form['mappings'][$i]['map']) as $subtarget) {
        $options = [];
        if (isset($form['mappings'][$i]['map'][$subtarget]['select']['#options'])) {
          $options = $form['mappings'][$i]['map'][$subtarget]['select']['#options'];
        }
        $form['mappings'][$i]['map'][$subtarget]['select']['#options'] = $this->getCustomSourceOptions() + $options;
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function mappingFormValidate(array &$form, FormStateInterface $form_state) {}

  /**
   * {@inheritdoc}
   */
  public function mappingFormSubmit(array &$form, FormStateInterface $form_state) {}

  /**
   * {@inheritdoc}
   */
  public function getSupportedCustomSourcePlugins(): array {
    return [];
  }

  /**
   * Returns a list of custom source options, used by the mapping form.
   *
   * @return array
   *   A list of custom source options using id => label.
   */
  protected function getCustomSourceOptions(): array {
    $custom_sources = [];
    $supported_custom_source_plugins = $this->getSupportedCustomSourcePlugins();
    // The blank source plugin is available for all parsers.
    $supported_custom_source_plugins[] = 'blank';

    foreach ($supported_custom_source_plugins as $custom_source_plugin_id) {
      $custom_source_plugin = $this->customSourcePluginManager->createInstance($custom_source_plugin_id, [
        'feed_type' => $this->feedType,
      ]);
      $custom_sources['custom__' . $custom_source_plugin_id] = $this->t('New @type source...', [
        '@type' => $custom_source_plugin->getLabel(),
      ]);
    }

    // In the UI, clearly separate the options for adding new sources from the
    // options for existing sources.
    if (!empty($custom_sources)) {
      $custom_sources_delimiter = ['----' => '----'];
    }
    else {
      $custom_sources_delimiter = [];
    }
    return $custom_sources + $custom_sources_delimiter;
  }

}
