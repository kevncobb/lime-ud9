<?php

namespace Drupal\symfony_mailer_bc\Plugin\EmailBuilder;

use Drupal\symfony_mailer\Processor\EmailProcessorBase;
use Drupal\symfony_mailer\EmailInterface;

/**
 * Defines the Email Builder plug-in for commerce module.
 *
 * @EmailBuilder(
 *   id = "commerce",
 *   sub_types = { "order_receipt" = @Translation("Order receipt") },
 * )
 *
 * @todo Notes for adopting Symfony Mailer into commerce. It should be possible
 * to remove the MailHandler service. Classes such as OrderReceiptMail could
 * call directly to EmailInterface or even be converted to an EmailBuilder. The
 * commerce_order_receipt template could be retired, switching instead to use
 * email__commerce__order_receipt.
 */
class CommerceEmailBuilder extends EmailProcessorBase {

  /**
   * {@inheritdoc}
   */
  public function preRender(EmailInterface $email) {
    $email->setSubject($email->getParam('subject'))
      ->setBody($email->getParam('body'));

    if ($from = $email->getParam('from')) {
      $email->setFrom($from);
    }

    if ($bcc = $email->getParam('bcc')) {
      $email->setBcc($bcc);
    }
  }

}
