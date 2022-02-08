<?php

namespace Drupal\addtocal\Plugin\AddToCal\Type;

use Drupal\addtocal\AddToCalTypeBase;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Routing\TrustedRedirectResponse;
use Drupal\Core\Url;

/**
 * Provides google calendar type.
 *
 * @AddToCalType(
 *   id = "google",
 *   label = @Translation("Google Calendar"),
 *   description = @Translation("Google calendar type.")
 * )
 */
class Google extends AddToCalTypeBase {

  /**
   * Generates a google calendar link
   *
   * @param array $info
   * @return \Drupal\Core\Url
   */
  public function generateStructure(array $info) {

    $url = Url::fromUri('http://www.google.com/calendar/event', [
      'query' => [
        'action' => 'TEMPLATE',
        'text' => $info['title'],
        'dates' => $info['rfc3339']['both'],
        'sprop' => 'website:' . $_SERVER['HTTP_HOST'],
        'location' => $info['location'],
        'details' => $info['description'],
      ],
    ]);

    return $url;
  }

  /**
   * @inheritdoc
   */
  public function downloadSubmit(EntityInterface $entity, array $settings, $delta, FormStateInterface $form_state) {
    $eventDetails = $this->extractEventDetails($entity, $settings, $delta);

    /** @var Url $url */
    $url = $this->generateStructure($eventDetails);

    // External URLS require to be a trusted response
    $response = new TrustedRedirectResponse($url->toString());
    $form_state->setResponse($response);
  }

}
