<?php

namespace Drupal\libraries_ui\Commands;

use Symfony\Component\Console\Helper\Table;
use Drupal\libraries_ui\LibrariesUiService;
use Drush\Commands\DrushCommands;

/**
 * Libraries UI Drush Commands.
 */
class LibrariesUiCommands extends DrushCommands {

  /**
   * The library UI service.
   *
   * @var \Drupal\libraries_ui\LibrariesUiService
   */
  protected $libraryUiService;

  /**
   * Constructs LibrariesUiCommands.
   *
   * @param \Drupal\libraries_ui\LibrariesUiService $libraries_ui
   *   The libraries UI service.
   */
  public function __construct(LibrariesUiService $libraries_ui) {
    parent::__construct();
    $this->libraryUiService = $libraries_ui;
  }

  /**
   * Libraries debug command.
   *
   * @command libraries:debug
   * @bootstrap full
   * @aliases ld
   *
   * @usage libraries:debug
   */
  public function librariesDebug() {
    $libraries = $this->libraryUiService->getAllLibraries();
    foreach ($libraries as $extension => $library) {
      $this->io()->writeln(PHP_EOL . $extension);
      $rows = [];
      foreach ($library as $group_name => $value) {
        $row = [];
        $row[] = $group_name;
        if (isset($value['version'])) {
          $row[] = $value['version'];
        }
        if (isset($value['dependencies'])) {
          $row[] = implode(',', $value['dependencies']);
        }
        $rows[] = $row;
      }
      $table = new Table($this->output);
      $table->setHeaders(['Name', 'Version', 'Dependencies'])->setRows($rows);
      $table->render();
    }
  }
}
