<?php

/**
 * @file
 * Documentation of Symfony Mailer back-compatibility hooks.
 */

/**
 * Alters back-compatibility creation of an email.
 *
 * The parameters supplied are from the old mail manager interface. The altered
 * values will be passed to the email factory.
 *
 * @param string $key
 *   The email sub-type, known as 'key' on the old interface.
 * @param array $params
 *   The email parameters.
 * @param array $context
 *   Array with entries:
 *   - module: The email type, known as 'module' on the old interface.
 *   - to: The email 'to' address.
 *   - reply: The email 'reply-to' address.
 *   - entity: The associated config entity, always null on the old interface.
 *
 * @see \Drupal\Core\Mail\MailManagerInterface::mail()
 * @see \Drupal\symfony_mailer\EmailFactory
 */
function hook_mailer_bc_alter(string &$key, array &$params, array &$context) {
}

/**
 * Alters back-compatibility creation of an email from a specific module.
 *
 * @param string $key
 *   The email sub-type, known as 'key' on the old interface.
 * @param array $params
 *   The email parameters.
 * @param array $context
 *   Array with entries:
 *   - module: The email type, known as 'module' on the old interface.
 *   - to: The email 'to' address.
 *   - reply: The email 'reply-to' address.
 *   - entity: The associated config entity, always null on the old interface.
 */
function hook_mailer_bc_MODULE_alter(string &$key, array &$params, array &$context) {
}
