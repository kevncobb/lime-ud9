<?php

/**
 * @file
 * Add to Cal hooks and documentation.
 */

/**
 * Alter the Add to Cal links element.
 *
 * @param array $links
 *   Render array that is to be used to display the Add to Cal links.
 * @param array $context
 *   Array with additional context information, containing:
 *   - items: FieldItemList object for the date field.
 *   - langcode: The language used to render the field.
 *   - delta: The current item from the items list that is being rendered.
 *
 * @see https://github.com/spatie/calendar-links
 *   Calendar links documentation.
 */
function hook_addtocal_links_alter(array &$links, array $context) {
  /** @var \Drupal\Core\Field\FieldItemListInterface $items */
  $items = $context['items'];

  // Alter Add to Cal links for nodes.
  if ($items->getEntity()->getEntityTypeId() == 'node') {
    /** @var \Spatie\CalendarLinks\Link $link */
    $link = $links['#addtocal_link'];

    // Update the event description value.
    $link->description('Your custom description');

    // Remove the ics download link.
    unset($links['#items']['ics']);

    /** @var \Spatie\CalendarLinks\Generator $generator */
    $generator = new \Your\Generator();

    // Add a new download link with a custom generator.
    $links['#items']['your_calendar'] = [
      'title' => $this->t('Your Calendar'),
      'aria-label' => $this->t('Add to Your Calendar'),
      'generator' => $generator,
    ];
  }
}
