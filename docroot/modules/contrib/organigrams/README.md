CONTENTS OF THIS FILE
----------------------

 * Introduction
 * Requirements
 * Plugins
 * Installation
 * Configuration
 * Browser support
 * References

INTRODUCTION
------------

Organigrams provides the ability to easily create and manage organization
charts, also knows as organigrams.

This module expands the taxonomy module. After installing, a button
"Add organigram" is added to the vocabulary page. This button creates
a vocabulary with additional fields required for creating an organigram.

Organigrams are highly customizable. Almost every aspect can be modified,
from border and background colors to horizontal spacing between items.
In addition, most of these settings can be overridden per item.

An organigram is drawn in an HTML5 canvas and is supported by all major
browsers. See the section 'BROWSER SUPPORT' section for more information.

One of Organigrams' key features is responsiveness. When an organigram has a
fixed width it will divide its children over multiple rows to take the given
width into account.
When the width of an organigram is set to match the width of its parent element,
the organigram will redraw itself on window resize and rearrange its children if
necessary (provided the parent element has a dynamic width).

In short, organigrams are:

 * Highly customizable
 * Easy to manage for editors
 * Responsive
 * Supported by all major browsers

REQUIREMENTS
------------

 This module requirements jQuery to work. If you want to make
 use of the token support, you will need the following modules:

  * Token: https://www.drupal.org/project/token
  * Token Filter: https://www.drupal.org/project/token_filter

PLUGINS
-------

 * ExplorerCanvas (excanvas)
   Provides HTML5 canvas support for Internet Explorer < 9.
   Downloaded from https://github.com/arv/ExplorerCanvas.

INSTALLATION
------------

 * Install as usual, see
   https://www.drupal.org/docs/8/extending-drupal-8/installing-contributed-modules-find-import-enable-configure-drupal-8
   for further information.

CONFIGURATION
-------------

 * Create organigrams in Administration >> Structure >> Taxonomy >> Add
   organigram.

 * Import Drupal 7 organigrams in Administration >> Structure >> Taxonomy >>
   Add organigram >> Import Drupal 7 Organigram.

 * As organigrams are actually vocabularies, the taxonomy permissions are
   honored.
   Configure organigrams specific user permissions in Administration >>
   People >> Permissions:

   - Create organigrams
     Can create organigrams.

   - Import Drupal 7 organigrams
     Can import Drupal 7 organigrams.

   - View organigrams
     Can view organigrams.

 * Organigrams are available as dedicated pages, blocks and tokens (requires
   the modules token (https://www.drupal.org/project/token) and token_filter
   (https://www.drupal.org/project/token_filter)).

BROWSER SUPPORT
---------------

 Organigrams is tested in the following browsers:

 * Google Chrome version 43.0.2357.124
 * Mozilla Firefox version 40.0.3
 * Microsoft Internet Explorer > version 7 (ExplorerCanvas is required for
   IE < 9, see the 'PLUGINS' section for more information)

REFERENCES
----------

 * Based on the Organigram module by Frederic Hennequin, see:
   https://www.drupal.org/sandbox/daeron/1603000

 * Extended with an improved version of the orgchart library by J. van Loenen,
   see: https://jvloenen.home.xs4all.nl/orgchart
