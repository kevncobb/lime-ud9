<?php

namespace Drupal\addtocal\Plugin\AddToCal\Type;

use Drupal\addtocal\AddToCalTypeBase;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Routing\TrustedRedirectResponse;
use Drupal\Core\Url;

/**
 * Provides generic calendar type (ics).
 *
 * @AddToCalType(
 *   id = "yahoo",
 *   label = @Translation("Yahoo Calendar"),
 *   description = @Translation("Yahoo calendar type.")
 * )
 */
class Yahoo extends AddToCalTypeBase {

  /**
   * Generates a Yahoo! calendar link
   *
   * @param array $info
   * @return \Drupal\Core\Url
   */
  public function generateStructure(array $info) {

    $url = Url::fromUri('http://calendar.yahoo.com/', [
      'query' => [
        'v' => 60,
        'TITLE' => $info['title'],
        'ST' => $info['rfc3339']['start'],
        'ET' => $info['rfc3339']['end'],
        'URL' => $_SERVER['HTTP_HOST'],
        'in_loc' => $info['location'],
        'desc' => $info['description'],
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
