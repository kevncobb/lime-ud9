<?php

namespace Drupal\addtocal\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a calendar plugin annotation object.
 *
 * @Annotation
 */
class AddToCalType extends Plugin {

  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The name of the calendar plugin.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $name;

}
