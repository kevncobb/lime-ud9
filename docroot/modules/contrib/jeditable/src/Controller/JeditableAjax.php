<?php

namespace Drupal\jeditable\Controller;

use Drupal\Component\Utility\Html;
use Drupal\Core\Controller\ControllerBase;
use Drupal\node\NodeStorageInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class JeditableAjax.
 *
 * @package Drupal\jeditable\Controller
 */
class JeditableAjax extends ControllerBase {

  protected $nodeStorage;

  /**
   * {@inheritdoc}
   */
  public function __construct(NodeStorageInterface $node_storage) {
    $this->nodeStorage = $node_storage;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.manager')->getStorage('node')
    );
  }

  /**
   * Saves latest changed value.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   Return Updated value.
   */
  public function jeditableAjaxSave() {
    $array = explode('-', $_POST['id']);
    // Fieldtype and $delta can used when expanding the scope of the module.
    list($type, $id, $field_name, $field_type, $delta) = $array;
    $value = Html::escape($_POST['value']);

    switch ($type) {
      case 'node':
        $node = $this->nodeStorage->load($id);
        $node->{$field_name}->value = $value;
        $node->save();
        return new Response($value);
    }
  }

}
