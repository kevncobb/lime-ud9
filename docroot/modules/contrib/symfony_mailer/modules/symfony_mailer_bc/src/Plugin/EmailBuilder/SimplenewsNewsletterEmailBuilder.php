<?php

namespace Drupal\symfony_mailer_bc\Plugin\EmailBuilder;

use Drupal\symfony_mailer\Processor\EmailProcessorBase;
use Drupal\symfony_mailer\EmailInterface;

/**
 * Defines the Email Builder plug-in for simplenews_newsletter entity.
 *
 * @EmailBuilder(
 *   id = "simplenews_newsletter",
 *   sub_types = {
 *     "node" = @Translation("Issue"),
 *     "test" = @Translation("Test"),
 *   },
 *   has_entity = TRUE,
 * )
 *
 * @todo Notes for adopting Symfony Mailer into simplenews. Can remove the
 * MailBuilder class, and many methods of MailEntity.
 */
class SimplenewsNewsletterEmailBuilder extends EmailProcessorBase {

  /**
   * {@inheritdoc}
   */
  public function preRender(EmailInterface $email) {
    /** @var \Drupal\simplenews\Mail\MailEntity $mail */
    $mail = $email->getParam('simplenews_mail');
    $email->setSubject($mail->getSubject())
      ->setBody($mail->getBody())
      ->addTextHeader('Precedence', 'bulk');

    if ($unsubscribe_url = \Drupal::token()->replace('[simplenews-subscriber:unsubscribe-url]', $email->getParams(), ['clear' => TRUE])) {
      $email->addTextHeader('List-Unsubscribe', "<$unsubscribe_url>");
    }
  }

}
