# Devel PHP

## Introduction

The Execute feature has been removed from the Devel module for Drupal 8 since
version 1.3. This module re-adds back that feature as an external module.

Go to the "Execute PHP Code" page (`/devel/php`), either using the Admin Toolbar
Tools module or using the Devel toolbar (see [Configuration](#configuration)
section) or using the Devel menu.

There is also a block you can place on your page if needed.

## Requirements

This module requires the following modules:
* [Devel](https://www.drupal.org/project/devel)

## Installation

* Install and enable this module like any other Drupal module.

## Configuration

The module has no settings or configuration.

Otherwise it uses configuration from the Devel module:
* On Devel configuration page (`/admin/config/development/devel`), in
  "Variables Dumper", you can choose how the output will behave.
* On Devel toolbar configuration page
  (`/admin/config/development/devel/toolbar`), you can enable "Execute PHP"
  menu link.

## Maintainers

Current maintainers:
* [Luca Lusso (lussoluca)](https://www.drupal.org/user/138068)
* [Florent Torregrosa (Grimreaper)](https://www.drupal.org/user/2388214)

This project has been sponsored by:
* [SparkFabrik](https://www.drupal.org/sparkfabrik)
