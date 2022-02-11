<?php

namespace Drupal\symfony_mailer_bc\Plugin\EmailBuilder;

use Drupal\symfony_mailer\Processor\EmailProcessorBase;
use Drupal\symfony_mailer\EmailInterface;

/**
 * Defines a base class for contact module email builders.
 * )
 */
class ContactEmailBuilderBase extends EmailProcessorBase {

  /**
   * {@inheritdoc}
   */
  public function preRender(EmailInterface $email) {
    /** @var \Drupal\user\UserInterface $sender */
    $sender = $email->getParam('sender');
    $contact_message = $email->getParam('contact_message');

    $email->appendBodyEntity($contact_message, 'mail')
      ->addLibrary('symfony_mailer_bc/contact')
      ->setVariable('subject', $contact_message->getSubject())
      ->setVariable('site_name', \Drupal::config('system.site')->get('name'))
      ->setVariable('sender_name', $sender->getDisplayName())
      ->setVariable('sender_url', $sender->isAuthenticated() ? $sender->toUrl('canonical')->toString() : $sender->getEmail());
  }

}
