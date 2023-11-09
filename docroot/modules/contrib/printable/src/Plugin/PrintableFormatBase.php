<?php

namespace Drupal\printable\Plugin;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Plugin\PluginBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Render\RenderContext;
use Drupal\Core\Render\RendererInterface;
use Drupal\printable\LinkExtractor\LinkExtractorInterface;
use Drupal\printable\PrintableCssIncludeInterface;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * Provides a base class for Filter plugins.
 */
abstract class PrintableFormatBase extends PluginBase implements PrintableFormatInterface, ContainerFactoryPluginInterface {

  /**
   * The config factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactory
   */
  protected $configFactory;

  /**
   * A render array of the content to be output by the printable format.
   *
   * @var array
   */
  protected $content;

  /**
   * A string containing the list of links present in the page.
   *
   * @var string
   */
  protected $footerContent;

  /**
   * Printable CSS include manager.
   *
   * @var \Drupal\printable\PrintableCssIncludeInterface
   */
  protected $printableCssInclude;

  /**
   * Printable link extractor.
   *
   * @var \Drupal\printable\LinkExtractor\LinkExtractorInterface
   */
  protected $linkExtractor;

  /**
   * The entity being rendered.
   *
   * @var \Drupal\Core\Entity\EntityInterface
   */
  protected $entity;

  /**
   * The Renderer service.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * The PDF generation status.
   *
   * @var string
   */
  protected $status = null;

  /**
   * {@inheritdoc}
   *
   * @param array $configuration
   *   The configuration array.
   * @param string $plugin_id
   *   The plugin ID.
   * @param array $plugin_definition
   *   The plugin definition.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory service.
   * @param \Drupal\printable\PrintableCssIncludeInterface $printable_css_include
   *   The printable CSS include manager.
   * @param \Drupal\printable\LinkExtractor\LinkExtractorInterface $link_extractor
   *   The link extractor.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The Renderer service.
   */
  public function __construct(array $configuration,
                              $plugin_id,
                              array $plugin_definition,
                              ConfigFactoryInterface $config_factory,
                              PrintableCssIncludeInterface $printable_css_include,
                              LinkExtractorInterface $link_extractor,
                              RendererInterface $renderer) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->configFactory = $config_factory;
    $this->printableCssInclude = $printable_css_include;
    $this->linkExtractor = $link_extractor;
    $this->configuration += $this->defaultConfiguration();
    $this->renderer = $renderer;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration, $plugin_id, $plugin_definition,
      $container->get('config.factory'),
      $container->get('printable.css_include'),
      $container->get('printable.link_extractor'),
      $container->get('renderer')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getLabel() {
    return $this->pluginDefinition['title'];
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->pluginDefinition['description'];
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function getConfiguration() {
    return $this->configuration;
  }

  /**
   * {@inheritdoc}
   */
  public function setConfiguration(array $configuration) {
    $this->configuration = $configuration;
    $this->configFactory->getEditable('printable.format')
      ->set($this->getPluginId(), $this->configuration)
      ->save();
  }

  /**
   * Set the configuration without saving it (API use).
   *
   * @param array $configuration
   *   The plugin configuration.
   */
  public function setConfigurationNoSave(array $configuration) {
    $this->configuration = $configuration;

    $library_config = $configuration['pdf_library_config'] ?? [];
    $this->pdfGenerator->setConfiguration($library_config);
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {
  }

  /**
   * {@inheritdoc}
   */
  public function setEntity(EntityInterface $entity) {
    $this->entity = $entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getEntity() {
    return $this->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function setContent(array $content) {
    $this->content = $content;
    $this->footerContent = NULL;
    if ($this->configFactory->get('printable.settings')
      ->get('list_attribute')) {

      $rendered = $this->renderer->executeInRenderContext(new RenderContext(),
        function () use ($content) {
          return $this->renderer->render($content);
        });
      $this->footerContent = $this->linkExtractor->listAttribute((string) $rendered);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getResponse() {
    return new Response($this->getOutput());
  }

  /**
   * Build a render array of the content, wrapped in the printable theme.
   *
   * @return array
   *   A render array representing the themed output of the content.
   */
  protected function buildContent() {
    $build = [
      '#theme' => ['printable__' . $this->getPluginId(), 'printable'],
      '#header' => [
        '#theme' => [
          'printable_header__' . $this->getPluginId(),
          'printable_header',
        ],
        '#logo_url' => theme_get_setting('logo.url'),
      ],
      '#content' => $this->content,
      '#footer' => [
        '#theme' => [
          'printable_footer__' . $this->getPluginId(),
          'printable_footer',
        ],
        '#footer_content' => $this->footerContent,
      ],
    ];

    if ($include_path = $this->printableCssInclude->getCssIncludePath()) {
      $build['#attached']['css'][] = $include_path;
    }

    return $build;
  }

  /**
   * Extracts the links present in HTML string.
   *
   * @param string $content
   *   The HTML of the page to be added.
   *
   * @return string
   *   The HTML string with presence of links depending on configuration.
   */
  protected function extractLinks($content) {
    switch ($this->configFactory->get('printable.settings')->get('extract_links')) {
      case 'extract':
        return $this->linkExtractor->extract($content);

      case 'remove':
        return $this->linkExtractor->removeAttribute($content, 'href');

      default:
        return $content;
    }
  }

  /**
   * Get a string representing the output of the generation process.
   *
   * @return string
   *   The output of this printable format (HTML or a file location).
   */
  public function getOutput() {
    $content = $this->buildContent();

    $content = $this->renderer->executeInRenderContext(new RenderContext(),
      function () use ($content) {
        return $this->renderer->render($content);
      });
    return $this->extractLinks($content);
  }

  /**
   * Get the PDF generation status.
   *
   * @return string
   *   A description of the result of seeking to generate the PDF.
   */
  public function getStatus() {
    return $this->status;
  }

}
