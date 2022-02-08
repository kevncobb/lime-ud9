<?php
namespace Drupal\views_slideshow_cycle2\Commands;

use Drush\Commands\DrushCommands;

/**
 * Drush commands for Views Slideshow Cycle2.
 */
class ViewsSlideshowCycle2Commands extends DrushCommands {

  /**
   * Download and install the jQuery Cycle2 library.
   *
   * @command views:slideshow:cycle2
   * @aliases dl-cycle2
   */
  public function downloadCycle2() {
    $this->installLibrary(
      'jQuery Cycle2',
      'libraries/jquery.cycle2/build',
      'jquery.cycle2.js',
      'http://malsup.github.io/jquery.cycle2.js'
    );
  }

  /**
   * Helper function to download a library in the given directory.
   * @see \Drupal\views_slideshow_cycle\Commands\ViewsSlideshowCycleCommands::installLibrary()
   */
  protected function installLibrary($name, $path, $filename, $url) {
    // Create the path if it does not exist.
    if (!is_dir($path)) {
      drush_op('mkdir', $path, 0755, TRUE);
      $this->logger()->info(dt('Directory @path was created', ['@path' => $path]));
    }

    // Be sure we can write in the directory.
    $perms = substr(sprintf('%o', fileperms($path)), -4);
    if ($perms !== '0755') {
      drush_shell_exec('chmod 755 ' . $path);
    }
    else {
      $perms = NULL;
    }

    $dir = getcwd();

    // Download the JavaScript file.
    if (is_file($path . '/' . $filename)) {
      $this->logger()->notice(dt('@name appears to be already installed.', [
        '@name' => $name,
      ]));
    }
    elseif (drush_op('chdir', $path) && drush_shell_exec('wget ' . $url)) {
      $this->logger()->success(dt('The latest version of @name has been downloaded to @path', [
        '@name' => $name,
        '@path' => $path,
      ]));
    }
    else {
      $this->logger()->warning(dt('Drush was unable to download the @name library to @path', [
        '@name' => $name,
        '@path' => $path,
      ]));
    }

    chdir($dir);

    // Restore the previous permissions.
    if ($perms) {
      drush_shell_exec('chmod ' . $perms . ' ' . $path);
    }
  }

}
