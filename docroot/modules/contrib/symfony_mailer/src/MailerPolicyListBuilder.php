<?php

namespace Drupal\symfony_mailer;

use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;

/**
 * Defines a class to build a listing of mailer policy entities.
 */
class MailerPolicyListBuilder extends ConfigEntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header = [
      'type' => $this->t('Type'),
      'sub_type' => $this->t('Sub-type'),
      'entity' => $this->t('Entity'),
      'summary' => $this->t('Summary'),
    ];
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $row = [
      'type' => $entity->getTypeLabel(),
      'sub_type' => $entity->getSubTypeLabel(),
      'entity' => $entity->getEntityLabel(),
      'summary' => $entity->getSummary(),
    ];
    return $row + parent::buildRow($entity);
  }

}
