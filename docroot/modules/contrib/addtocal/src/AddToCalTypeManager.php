<?php
namespace Drupal\addtocal;

use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;

class AddToCalTypeManager extends DefaultPluginManager {

  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct('Plugin/AddToCal/Type', $namespaces, $module_handler, 'Drupal\addtocal\AddToCalTypeInterface', 'Drupal\addtocal\Annotation\AddToCalType');
    $this->alterInfo('addtocal_type_info');
    $this->setCacheBackend($cache_backend, 'addtocal_types');
  }
}
