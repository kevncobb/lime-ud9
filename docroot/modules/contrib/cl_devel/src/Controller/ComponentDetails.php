<?php

declare(strict_types=1);

namespace Drupal\cl_devel\Controller;

use Drupal\Component\Render\MarkupInterface;
use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\File\FileUrlGeneratorInterface;
use Drupal\Core\Template\Attribute;
use Drupal\sdc\ComponentPluginManager;
use Drupal\sdc\Exception\ComponentNotFoundException;
use Drupal\sdc\Plugin\Component;
use League\CommonMark\CommonMarkConverter;
use League\CommonMark\Exception\CommonMarkException;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Controller to render the details of the controller.
 */
final class ComponentDetails extends ControllerBase {

  /**
   * Creates a new ComponentDetails object.
   *
   * @param \Drupal\sdc\ComponentPluginManager $pluginManager
   *   The component plugin manager.
   */
  public function __construct(
    private readonly ComponentPluginManager $pluginManager,
    protected FileUrlGeneratorInterface $fileUrlGenerator,
  ) {
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): self {
    return new self($container->get('plugin.manager.sdc'), $container->get('file_url_generator'));
  }

  /**
   * Renders the details of the component.
   *
   * @param string $component_id
   *   The machine name of the component.
   *
   * @return array
   *   The render array with the component details.
   */
  public function details(string $component_id): array {
    $component = $this->getComponent($component_id);
    if (!$component) {
      return [
        '#markup' => $this->t('There was an error. Unable to find the selected component.'),
      ];
    }
    $metadata = $component->metadata;
    $converter = new CommonMarkConverter();
    try {
      $docs = $converter->convert($metadata->documentation);
    }
    catch (CommonMarkException $e) {
      $docs = $metadata->documentation;
    }
    $thumbnail_path = $metadata->getThumbnailPath();
    $href = $thumbnail_path
      ? $this->fileUrlGenerator->generateAbsoluteString($thumbnail_path)
      : '';
    return [
      '#type' => 'component',
      '#component' => 'cl_devel:component-details',
      '#props' => [
        'attributes' => new Attribute(),
        'machineName' => $metadata->machineName,
        'id' => $component_id,
        'name' => $metadata->name,
        'description' => $metadata->description,
        'status' => $metadata->status,
        'thumbnailHref' => $href,
        'path' => $metadata->path,
        'props' => $metadata->schema ?? new \stdClass(),
        'slots' => $metadata->slots,
      ],
      '#slots' => [
        'documentation' => [
          '#markup' => Xss::filterAdmin($docs->getContent()),
        ],
      ],
    ];
  }

  /**
   * Renders the title of the page.
   *
   * @param string $component_id
   *   The component ID.
   *
   * @return \Drupal\Component\Render\MarkupInterface
   *   The title.
   */
  public function title(string $component_id): MarkupInterface {
    $component = $this->getComponent($component_id);
    return $component
      ? $this->t('Component %name', ['%name' => $component->metadata->name])
      : $this->t('There was an error. Unable to find the selected component.');
  }

  /**
   * Gets the component based on the ID.
   *
   * @param string $component_id
   *   The component ID.
   *
   * @return \Drupal\sdc\Plugin\Component|null
   *   The component or NULL if it cannot be found.
   */
  private function getComponent(string $component_id): ?Component {
    try {
      return $this->pluginManager->find($component_id);
    }
    catch (ComponentNotFoundException $e) {
      $this->messenger()->addError(
        $this->t('There is no component with ID "@component_id". Re-check the ID and try again.')
      );
    }
    return NULL;
  }

}
