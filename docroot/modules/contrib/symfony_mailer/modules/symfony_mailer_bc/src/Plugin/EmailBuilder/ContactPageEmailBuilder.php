<?php

namespace Drupal\symfony_mailer_bc\Plugin\EmailBuilder;

use Drupal\Core\Url;
use Drupal\symfony_mailer\EmailInterface;

/**
 * Defines the Email Builder plug-in for contact module page forms.
 *
 * @EmailBuilder(
 *   id = "contact_form",
 *   sub_types = {
 *     "mail" = @Translation("Message"),
 *     "copy" = @Translation("Sender copy"),
 *     "autoreply" = @Translation("Auto-reply"),
 *   },
 *   has_entity = TRUE,
 * )
 *
 * @todo Notes for adopting Symfony Mailer into Drupal core. This builder can
 * set langcode, to, reply-to so the calling code doesn't need to.
 */
class ContactPageEmailBuilder extends ContactEmailBuilderBase {

  /**
   * {@inheritdoc}
   */
  public function preRender(EmailInterface $email) {
    parent::preRender($email);
    $email->setVariable('form', $email->getEntity()->label())
      ->setVariable('form_url', Url::fromRoute('<current>')->toString());

    if ($email->getSubType() == 'autoreply') {
      $email->setBody($email->getParam('contact_form')->getReply());
    }
  }

}
