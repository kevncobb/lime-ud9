<?php

namespace Drupal\libraries_ui;

use Drupal\Core\Asset\LibraryDiscovery;
use Drupal\Core\Extension\ModuleHandler;
use Drupal\Core\Extension\ThemeHandler;

/**
 * Libraries Ui Service.
 *
 * @package Drupal\libraries_ui
 */
class LibrariesUiService {

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandler
   */
  protected $moduleHandler;

  /**
   * The theme handler.
   *
   * @var \Drupal\Core\Extension\ThemeHandler
   */
  protected $themeHandler;

  /**
   * The library discovery.
   *
   * @var \Drupal\Core\Asset\LibraryDiscovery
   */
  protected $libraryDiscovery;

  /**
   * The Constructor.
   *
   * @param \Drupal\Core\Extension\ModuleHandler $module_handler
   *   The module handler service.
   * @param \Drupal\Core\Extension\ThemeHandler $theme_handler
   *   The theme handler service.
   * @param \Drupal\Core\Asset\LibraryDiscovery $library_discovery
   *   The library discovery service.
   */
  public function __construct(
    ModuleHandler $module_handler,
    ThemeHandler $theme_handler,
    LibraryDiscovery $library_discovery
  ) {
    $this->moduleHandler = $module_handler;
    $this->themeHandler = $theme_handler;
    $this->libraryDiscovery = $library_discovery;
  }

  /**
   * Get all libraries.
   *
   * @return array
   *   Returns an array of libraries.
   */
  public function getAllLibraries(): array {
    $modules = $this->moduleHandler->getModuleList();
    $themes = $this->themeHandler->rebuildThemeData();
    $extensions = array_merge($modules, $themes);
    // phpcs:ignore
    $root = \Drupal::root();
    $libraries = ['core' => $this->libraryDiscovery->getLibrariesByExtension('core')];
    foreach ($extensions as $extension_name => $extension) {
      $library_file = $extension->getPath() . '/' . $extension_name . '.libraries.yml';
      if (is_file($root . '/' . $library_file)) {
        $libraries[$extension_name] = $this->libraryDiscovery->getLibrariesByExtension($extension_name);
      }
    }
    return $libraries;
  }

}
