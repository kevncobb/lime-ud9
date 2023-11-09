<?php

namespace Drupal\libraries_ui\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\libraries_ui\LibrariesUiService;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Libraries Ui Controller.
 *
 * @package Drupal\libraries_ui\Controller
 */
class LibrariesUiController extends ControllerBase {

  /**
   * The libraries service.
   *
   * @var \Drupal\libraries_ui\LibrariesUiService
   */
  protected $librariesUiService;

  /**
   * The Constructor.
   *
   * @param \Drupal\libraries_ui\LibrariesUiService $libraries_ui
   *   The libraries' ui service.
   */
  public function __construct(LibrariesUiService $libraries_ui) {
    $this->librariesUiService = $libraries_ui;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('libraries_ui')
    );
  }

  /**
   * Libraries UI.
   *
   * @return array
   *   Information Libraries UI info.
   */
  public function libraries(): array {
    return [
      '#theme' => 'libraries_ui',
      '#libraries' => $this->librariesUiService->getAllLibraries(),
    ];
  }

}
