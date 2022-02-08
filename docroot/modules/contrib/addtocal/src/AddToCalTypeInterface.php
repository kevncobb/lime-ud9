<?php

namespace Drupal\addtocal;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Component\Plugin\PluginInspectionInterface;

interface AddToCalTypeInterface extends PluginInspectionInterface, ContainerFactoryPluginInterface {

  /**
   * Return the name of the type plugin.
   *
   * @return string
   */
  public function getName();

  /**
   * @param array $eventDetails
   * @return mixed
   */
  public function generateStructure(array $eventDetails);

  /**
   * @param \Drupal\Core\Entity\EntityInterface $entity
   * @param array $settings
   * @param int $delta
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   * @return mixed
   */
  public function downloadSubmit(EntityInterface $entity, array $settings, $delta, FormStateInterface $form_state);
}
