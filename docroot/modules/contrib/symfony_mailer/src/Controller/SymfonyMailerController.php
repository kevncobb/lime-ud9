<?php

namespace Drupal\symfony_mailer\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\symfony_mailer\MailerTransportInterface;

/**
 * Route controller for symfony mailer.
 */
class SymfonyMailerController extends ControllerBase {

  /**
   * Sets the transport as the default.
   *
   * @param \Drupal\symfony_mailer\Entity\MailerTransport $mailer_transport
   *   The mailer transport entity.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   A redirect to the transport listing page.
   */
  public function setAsDefault(MailerTransportInterface $mailer_transport) {
    $mailer_transport->setAsDefault();
    $this->messenger()->addStatus($this->t('The default transport is now %label.', ['%label' => $mailer_transport->label()]));
    return $this->redirect('entity.mailer_transport.collection');
  }

}
