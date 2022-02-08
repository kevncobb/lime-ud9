<?php

namespace Drupal\feedback\Entity;

use Drupal\views\EntityViewsData;
use Drupal\views\EntityViewsDataInterface;

/**
 * Provides Views data for Feedback message entities.
 */
class FeedbackMessageViewsData extends EntityViewsData implements EntityViewsDataInterface {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    $data['feedback_message']['table']['base'] = [
      'field' => 'id',
      'title' => $this->t('Feedback message'),
      'help' => $this->t('The Feedback message ID.'),
    ];

    return $data;
  }

}
