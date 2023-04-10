# Expand Collapse Formatter

This is a simple module to expand or collapse long texts. It provides a
configurable formatter which can be attached to each text area field of an
entity.

For a full description of the module, visit the
[project page](https://www.drupal.org/project/expand_collapse_formatter).

Submit bug reports and feature suggestions, or track changes in the
[issue queue](https://www.drupal.org/project/issues/expand_collapse_formatter).


## Table of contents

- Requirements
- Installation
- Configuration
- Maintainers


## Requirements

This module only works on certain field types:
- "`text_long`"
- "`string_long`"
- "`text_with_summary`"

## Installation

Install as you would normally install a contributed Drupal module. For further
information, see
[Installing Drupal Modules](https://www.drupal.org/docs/extending-drupal/installing-drupal-modules).


## Configuration

1. After enabling this module, navigate to your entity type's `"manage display"` tab.
2. Choose the `"Expand collapse formatter"`
3. Adjust the settings using the gear icon. You can change the `"trim length"`,
   `"default state"`, open and close text, and the classes used for each action.
   Click `"Update"` to save these settings and then remember to click `"Save"` at the
   bottom of the page.

After these steps, you should see the "read more" link at the bottom of the
fields with this formatter, so long as they contain more characters than the
trim length.


## Maintainers

- Daniel Cothran - [andileco](https://www.drupal.org/u/andileco)
- jeroen_drenth - [jeroen_drenth](https://www.drupal.org/u/jeroen_drenth)
