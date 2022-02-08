CONTENTS OF THIS FILE
---------------------
 * Introduction
 * Requirements
 * Installation
 * Configuration
 * Maintainers

INTRODUCTION
------------
This module controls the visibility of unpublished paragraphs for authenticated
users with the right permission.

 * For a full description of the module, visit the project page:
   https://www.drupal.org/project/unpublished_paragraphs

 * To submit bug reports and feature suggestions, or track changes:
   https://www.drupal.org/project/issues/unpublished_paragraphs

REQUIREMENTS
------------
  * Paragraphs (https://www.drupal.org/project/paragraphs)

INSTALLATION
------------
Install as you would normally install a contributed Drupal module. For further
information, see: https://www.drupal.org/docs/8/extending-drupal-8/installing-modules

From command line 

```$bash
composer require drupal/unpublished_paragraphs
drush en unpublished_paragraphs
```

CONFIGURATION
-------------
 * Install and enable the module (see instructions above)
 * Edit a piece of content with Paragraphs
 * Un-check the "Published" button on one of your Paragraphs
 * Save and view the node
 * You should see a button that says "Toggle visibility of unpublished items" in the lower right corner of the page.
 * Click the button to make unpublished Paragraphs appear and disappear.

MAINTAINERS
-----------
Current maintainer:
  * Unity Technologies - https://www.drupal.org/unity-technologies