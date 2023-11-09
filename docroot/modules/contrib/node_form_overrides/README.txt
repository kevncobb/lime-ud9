CONTENTS OF THIS FILE
---------------------

 * Introduction
 * Requirements
 * Installation
 * Configuration
 * Maintainers


INTRODUCTION
------------

Drupal 8 module to override the default node create, update, and delete form
titles and button labels.

Often times when building a Drupal site I find myself using hook_form_alter to
change the page titles and button labels based on certain conditions for that
content type. This module aims to make that easier by putting some of these
common overrides in the config of the content type.

Currently this only supports node forms, but I may expand it to support other
entities in the future.

Suggestions and patches welcome.

 * For the description of the module visit:
   https://www.drupal.org/project/node_form_overrides

 * To submit bug reports and feature suggestions, or to track changes visit:
   https://www.drupal.org/project/issues/node_form_overrides


REQUIREMENTS
------------

This module requires the node and token Drupal modules.


INSTALLATION
------------

Install the Node Form Overrides module as you would normally install a
contributed Drupal module. Visit https://www.drupal.org/node/1897420 for further
information.


CONFIGURATION
-------------

    1. Navigate to Administration > Content Types and click the content type
       you want override, edit the fields under the "Label Overrides" tab and
       save your changes.

MAINTAINERS
-----------

 * loze - https://www.drupal.org/u/loze

Supporting organization:

 * ILG Studios - https://ilgstudio.com
