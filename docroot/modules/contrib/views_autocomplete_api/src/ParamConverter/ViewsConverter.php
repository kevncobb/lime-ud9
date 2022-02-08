<?php

namespace Drupal\views_autocomplete_api\ParamConverter;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\Routing\Route;
use Drupal\Core\ParamConverter\ParamConverterInterface;

/**
 * Class ViewsConverter, load views by name.
 *
 * @package Drupal\views_autocomplete_api\ParamConverter
 */
class ViewsConverter implements ParamConverterInterface {

  /**
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * {@inheritDoc}
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritDoc}
   */
  public function convert($value, $definition, $name, array $defaults) {
    $views_name = explode(',', $value);

    if (!empty($views_name)) {
      // Merge with empty value.
      $views_name_empty = array_fill_keys($views_name, '');
      return array_merge(
        $views_name_empty,
        $this->entityTypeManager->getStorage('view')
          ->loadMultiple($views_name)
      );
    }
    return [];
  }

  /**
   * {@inheritDoc}
   */
  public function applies($definition, $name, Route $route) {
    return !empty($definition['type']) && $definition['type'] === 'views-autocomplete-api-views';
  }

}
